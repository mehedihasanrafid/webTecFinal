<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/student_ms/database/database.php";
require_once $path . "/student_ms/database/sessionDetails.php";
require_once $path . "/student_ms/database/facultyDetails.php";
require_once $path . "/student_ms/database/courseRegistrationDetails.php";
require_once $path . "/student_ms/database/marksDetails.php";

// Enable error display for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "getSession") {
        $dbo = new Database();
        $sobj = new SessionDetails();
        $rv = $sobj->getSessions($dbo);
        echo json_encode($rv);
    }

    if ($action == "getFacultyCourses") {
        $facid = $_POST['facid'];
        $sessionid = $_POST['sessionid'];
        $dbo = new Database();
        $fo = new faculty_details();
        $rv = $fo->getCoursesInASession($dbo, $sessionid, $facid);
        echo json_encode($rv);
    }

    if ($action == "getStudentList") {
        $sessionid = $_POST['sessionid'];
        $courseid = $_POST['courseid'];
        $facid = $_POST['facid'];

        $dbo = new Database();
        $crgo = new CourseRegistrationDetails();
        $students = $crgo->getRegisteredStudents($dbo, $sessionid, $courseid);

        $mobj = new marksDetails();
        $marksData = $mobj->getMarksList($dbo, $sessionid, $courseid);

        foreach ($students as &$student) {
            $student['marks'] = '';
            $student['grade'] = '';
            foreach ($marksData as $md) {
                if ($student['id'] == $md['student_id']) {
                    $student['marks'] = $md['marks'];
                    $student['grade'] = $md['grade'];
                    break;
                }
            }
        }

        echo json_encode($students);
    }

    if ($action == "saveMarks") {
        $sessionid = $_POST['sessionid'];
        $courseid = $_POST['courseid'];
        $studentid = $_POST['studentid'];
        $marks = $_POST['marks'];
        $grade = $_POST['grade'];

        $dbo = new Database();
        $mobj = new marksDetails();
        $rv = $mobj->saveMarks($dbo, $sessionid, $courseid, $studentid, $marks, $grade);
        echo json_encode($rv);
    }

    if ($action == "downloadCSV") {
        $sessionid = $_GET['sessionid'];
        $courseid = $_GET['courseid'];

        $dbo = new Database();
        $mobj = new marksDetails();
        $data = $mobj->getMarksList($dbo, $sessionid, $courseid);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="marks_report.csv"');
        $output = fopen("php://output", "w");
        fputcsv($output, ['Student ID', 'Marks', 'Grade']);
        foreach ($data as $row) {
            fputcsv($output, [$row['student_id'], $row['marks'], $row['grade']]);
        }
        fclose($output);
        exit();
    }
}
?>
