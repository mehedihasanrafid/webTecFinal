<?php
session_start();
if (!isset($_SESSION['current_user']) || $_SESSION['is_admin'] != 1) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/student_ms/database/database.php";
require_once $path . "/student_ms/database/facultyDetails.php";
require_once $path . "/student_ms/database/sessionDetails.php";

// You may create these classes for course and allotment or implement inline here
class CourseDetails
{
    private $conn;
    public function __construct($dbo)
    {
        $this->conn = $dbo->conn;
    }

    public function getCoursesBySession($session_id)
    {
        $rv = [];
        $c = "SELECT cd.* FROM course_details cd
              INNER JOIN course_allotment ca ON cd.id = ca.course_id
              WHERE ca.session_id = :session_id
              GROUP BY cd.id";
        $s = $this->conn->prepare($c);
        try {
            $s->execute([":session_id" => $session_id]);
            $rv = $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
        }
        return $rv;
    }

    public function addCourse($code, $title, $credit)
    {
        $c = "INSERT INTO course_details (code, title, credit) VALUES (:code, :title, :credit)";
        $s = $this->conn->prepare($c);
        try {
            $s->execute([":code" => $code, ":title" => $title, ":credit" => $credit]);
            return $this->conn->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }
}

class CourseAllotment
{
    private $conn;
    public function __construct($dbo)
    {
        $this->conn = $dbo->conn;
    }

    public function assignClass($faculty_id, $course_id, $session_id)
    {
        // Check if already assigned
        $check = "SELECT * FROM course_allotment WHERE faculty_id=:faculty_id AND course_id=:course_id AND session_id=:session_id";
        $s = $this->conn->prepare($check);
        $s->execute([":faculty_id" => $faculty_id, ":course_id" => $course_id, ":session_id" => $session_id]);
        if ($s->rowCount() > 0) {
            return false; // Already assigned
        }

        $c = "INSERT INTO course_allotment (faculty_id, course_id, session_id) VALUES (:faculty_id, :course_id, :session_id)";
        $s = $this->conn->prepare($c);
        try {
            $s->execute([":faculty_id" => $faculty_id, ":course_id" => $course_id, ":session_id" => $session_id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

$dbo = new Database();
$facultyObj = new faculty_details();
$sessionObj = new SessionDetails();
$courseObj = new CourseDetails($dbo);
$allotmentObj = new CourseAllotment($dbo);

$action = $_POST['action'] ?? '';

switch ($action) {
    case "addSession":
        $year = intval($_POST['year'] ?? 0);
        $term = trim($_POST['term'] ?? '');

        if ($year <= 0 || !in_array($term, ['Fall', 'Summer', 'Spring'])) {
            echo json_encode(["status" => "error", "message" => "Invalid year or term"]);
            exit;
        }

        // Check if session exists
        $sessions = $sessionObj->getSessions($dbo);
        foreach ($sessions as $session) {
            if ($session['year'] == $year && strtolower($session['term']) == strtolower($term)) {
                echo json_encode(["status" => "error", "message" => "Session already exists"]);
                exit;
            }
        }

        // Insert new session
        $c = "INSERT INTO session_details (year, term) VALUES (:year, :term)";
        $s = $dbo->conn->prepare($c);
        try {
            $s->execute([":year" => $year, ":term" => $term]);
            echo json_encode(["status" => "success", "message" => "Session added successfully"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Failed to add session"]);
        }
        break;

    case "getSessions":
        $sessions = $sessionObj->getSessions($dbo);
        echo json_encode($sessions);
        break;

    case "addClass":
        $year = intval($_POST['year'] ?? 0);
        $term = trim($_POST['term'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $credit = intval($_POST['credit'] ?? 0);

        if ($year <= 0 || !in_array($term, ['Fall', 'Summer', 'Spring']) || empty($code) || empty($title) || $credit <= 0) {
            echo json_encode(["status" => "error", "message" => "Invalid input"]);
            exit;
        }

        // Find session id
        $sessions = $sessionObj->getSessions($dbo);
        $session_id = null;
        foreach ($sessions as $session) {
            if ($session['year'] == $year && strtolower($session['term']) == strtolower($term)) {
                $session_id = $session['id'];
                break;
            }
        }
        if (!$session_id) {
            echo json_encode(["status" => "error", "message" => "Session not found"]);
            exit;
        }

        // Add course
        $course_id = $courseObj->addCourse($code, $title, $credit);
        if (!$course_id) {
            echo json_encode(["status" => "error", "message" => "Failed to add course"]);
            exit;
        }

        // Assign course to session (course_allotment)
        $c = "INSERT INTO course_allotment (faculty_id, course_id, session_id) VALUES (0, :course_id, :session_id)";
        // faculty_id=0 means unassigned course for that session
        $s = $dbo->conn->prepare($c);
        try {
            $s->execute([":course_id" => $course_id, ":session_id" => $session_id]);
            echo json_encode(["status" => "success", "message" => "Class added successfully"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Failed to assign class to session"]);
        }
        break;

    case "getClassesBySession":
        $session_id = intval($_POST['session_id'] ?? 0);
        if ($session_id <= 0) {
            echo json_encode([]);
            exit;
        }
        $courses = $courseObj->getCoursesBySession($session_id);
        echo json_encode($courses);
        break;

    case "addFaculty":
        $user_name = trim($_POST['user_name'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $is_admin = intval($_POST['is_admin'] ?? 0);

        if (empty($user_name) || empty($name) || empty($password)) {
            echo json_encode(["status" => "error", "message" => "All fields are required"]);
            exit;
        }

        // Check if username exists
        $c = "SELECT id FROM faculty_details WHERE user_name=:user_name";
        $s = $dbo->conn->prepare($c);
        $s->execute([":user_name" => $user_name]);
        if ($s->rowCount() > 0) {
            echo json_encode(["status" => "error", "message" => "Username already exists"]);
            exit;
        }

        // Insert faculty (password stored as plain text here, consider hashing in production)
        $c = "INSERT INTO faculty_details (user_name, name, password, is_admin) VALUES (:user_name, :name, :password, :is_admin)";
        $s = $dbo->conn->prepare($c);
        try {
            $s->execute([":user_name" => $user_name, ":name" => $name, ":password" => $password, ":is_admin" => $is_admin]);
            echo json_encode(["status" => "success", "message" => "Faculty added successfully"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Failed to add faculty"]);
        }
        break;

    case "getFaculties":
        $c = "SELECT id, user_name, name FROM faculty_details";
        $s = $dbo->conn->prepare($c);
        try {
            $s->execute();
            $faculties = $s->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($faculties);
        } catch (Exception $e) {
            echo json_encode([]);
        }
        break;

    case "assignClass":
        $session_id = intval($_POST['session_id'] ?? 0);
        $course_id = intval($_POST['course_id'] ?? 0);
        $faculty_id = intval($_POST['faculty_id'] ?? 0);

        if ($session_id <= 0 || $course_id <= 0 || $faculty_id <= 0) {
            echo json_encode(["status" => "error", "message" => "Invalid input"]);
            exit;
        }

        $success = $allotmentObj->assignClass($faculty_id, $course_id, $session_id);
        if ($success) {
            echo json_encode(["status" => "success", "message" => "Class assigned successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Class already assigned or failed"]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
        break;
}
