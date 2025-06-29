<?php
// student_ms/database/marksDetails.php

// Ensure Database class is available
if (!class_exists('Database')) {
    require_once __DIR__ . "/database.php";
}

class MarksDetails {
    /**
     * Saves or updates marks for a student in a specific course and session.
     *
     * @param Database $dbo The Database object.
     * @param int $sessionId The ID of the session.
     * @param int $courseId The ID of the course.
     * @param int $facultyId The ID of the faculty.
     * @param int $studentId The ID of the student.
     * @param float $marks The marks obtained by the student (0-100).
     * @return bool True on success, false on failure.
     * @throws Exception If marks are out of valid range.
     */
    public function saveMarks(Database $dbo, int $sessionId, int $courseId, int $facultyId, int $studentId, float $marks): bool {
        // Validate marks range
        if ($marks < 0 || $marks > 100) {
            // Throw an exception so the AJAX handler can catch it and return a 400 error
            throw new Exception("Marks must be a number between 0 and 100.");
        }

        // Check if marks already exist for this student, course, session, and faculty
        $checkSql = "SELECT COUNT(*) FROM marks_details 
                     WHERE session_id = :session_id 
                     AND course_id = :course_id 
                     AND faculty_id = :faculty_id 
                     AND student_id = :student_id";
        $checkStmt = $dbo->prepare($checkSql);
        
        try {
            $checkStmt->execute([
                ":session_id" => $sessionId,
                ":course_id" => $courseId,
                ":faculty_id" => $facultyId,
                ":student_id" => $studentId
            ]);
            $exists = $checkStmt->fetchColumn() > 0;

            if ($exists) {
                // Update existing marks
                $updateSql = "UPDATE marks_details 
                              SET marks = :marks
                              WHERE session_id = :session_id 
                              AND course_id = :course_id 
                              AND faculty_id = :faculty_id 
                              AND student_id = :student_id";
                $updateStmt = $dbo->prepare($updateSql);
                $updateStmt->execute([
                    ":marks" => $marks,
                    ":session_id" => $sessionId,
                    ":course_id" => $courseId,
                    ":faculty_id" => $facultyId,
                    ":student_id" => $studentId
                ]);
                return true;
            } else {
                // Insert new marks
                $insertSql = "INSERT INTO marks_details 
                              (session_id, course_id, faculty_id, student_id, marks) 
                              VALUES 
                              (:session_id, :course_id, :faculty_id, :student_id, :marks)";
                $insertStmt = $dbo->prepare($insertSql);
                $insertStmt->execute([
                    ":session_id" => $sessionId,
                    ":course_id" => $courseId,
                    ":faculty_id" => $facultyId,
                    ":student_id" => $studentId,
                    ":marks" => $marks
                ]);
                return true;
            }
        } catch (PDOException $e) {
            error_log("Error saving marks for student {$studentId} in course {$courseId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves marks for all students in a specific course and session, assigned by a faculty.
     *
     * @param Database $dbo The Database object.
     * @param int $sessionId The ID of the session.
     * @param int $courseId The ID of the course.
     * @param int $facultyId The ID of the faculty.
     * @return array An array of marks entries (student_id, marks), or an empty array on failure.
     */
    public function getMarksList(Database $dbo, int $sessionId, int $courseId, int $facultyId): array {
        $marksList = [];
        $sql = "SELECT student_id, marks 
                FROM marks_details 
                WHERE session_id = :session_id 
                AND course_id = :course_id 
                AND faculty_id = :faculty_id";
        $stmt = $dbo->prepare($sql);
        try {
            $stmt->execute([
                ":session_id" => $sessionId,
                ":course_id" => $courseId,
                ":faculty_id" => $facultyId
            ]);
            $marksList = $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching marks list for course {$courseId} in session {$sessionId}: " . $e->getMessage());
        }
        return $marksList;
    }
}
?>
