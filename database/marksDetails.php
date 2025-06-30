<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/student_ms/database/database.php";

class marksDetails
{
    public function saveMarks($dbo, $session, $course, $student, $marks, $grade)
    {
        $rv = [-1];
        $query = "INSERT INTO marks_details(session_id, course_id, student_id, marks, grade)
                  VALUES (:session_id, :course_id, :student_id, :marks, :grade)";
        $stmt = $dbo->conn->prepare($query);
        try {
            $stmt->execute([
                ":session_id" => $session,
                ":course_id" => $course,
                ":student_id" => $student,
                ":marks" => $marks,
                ":grade" => $grade
            ]);
            $rv = [1];
        } catch (Exception $e) {
            // If already exists, update instead
            $query = "UPDATE marks_details SET marks = :marks, grade = :grade
                      WHERE session_id = :session_id AND course_id = :course_id AND student_id = :student_id";
            $stmt = $dbo->conn->prepare($query);
            try {
                $stmt->execute([
                    ":session_id" => $session,
                    ":course_id" => $course,
                    ":student_id" => $student,
                    ":marks" => $marks,
                    ":grade" => $grade
                ]);
                $rv = [1];
            } catch (Exception $ee) {
                $rv = [$ee->getMessage()];
            }
        }
        return $rv;
    }

    public function getMarksList($dbo, $session, $course)
    {
        $rv = [];
        $query = "SELECT student_id, marks, grade FROM marks_details 
                  WHERE session_id = :session_id AND course_id = :course_id";
        $stmt = $dbo->conn->prepare($query);
        try {
            $stmt->execute([
                ":session_id" => $session,
                ":course_id" => $course
            ]);
            $rv = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Optionally log the error
        }
        return $rv;
    }
}
?>
