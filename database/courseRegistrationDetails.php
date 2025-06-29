<?php
// student_ms/database/courseRegistrationDetails.php

// Ensure Database class is available
if (!class_exists('Database')) {
    require_once __DIR__ . "/database.php";
}

class CourseRegistrationDetails {
    /**
     * Retrieves a list of students registered for a specific course in a given session.
     *
     * @param Database $dbo The Database object.
     * @param int $sessionId The ID of the session.
     * @param int $courseId The ID of the course.
     * @return array An array of student details (id, roll_no, name), or an empty array on failure.
     */
    public function getRegisteredStudents(Database $dbo, int $sessionId, int $courseId): array {
        $students = [];
        $sql = "SELECT sd.id, sd.roll_no, sd.name 
                FROM student_details AS sd
                JOIN course_registration AS crg ON crg.student_id = sd.id
                WHERE crg.session_id = :session_id AND crg.course_id = :course_id
                ORDER BY sd.roll_no ASC";
        $stmt = $dbo->prepare($sql);
        try {
            $stmt->execute([
                ":session_id" => $sessionId,
                ":course_id" => $courseId
            ]);
            $students = $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching registered students for course {$courseId} in session {$sessionId}: " . $e->getMessage());
        }
        return $students;
    }
}
?>
