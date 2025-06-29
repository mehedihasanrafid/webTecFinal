<?php
// student_ms/database/facultyDetails.php

// Ensure Database class is available
if (!class_exists('Database')) {
    require_once __DIR__ . "/database.php";
}

class faculty_details {
    /**
     * Verifies user credentials against the database.
     *
     * @param Database $dbo The Database object.
     * @param string $username The username to verify.
     * @param string $password The plain-text password to verify.
     * @return array An associative array with 'id' and 'status' (ALL OK, Wrong password, USER NAME DOES NOT EXIST, Database error).
     */
    public function verifyUser (Database $dbo, string $username, string $password): array {
        $response = ["id" => -1, "status" => "ERROR"];
        $sql = "SELECT id, password FROM faculty_details WHERE user_name = :username";
        $stmt = $dbo->prepare($sql);
        try {
            $stmt->execute([":username" => $username]);
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch();
                // Directly compare the plain-text password
                if ($result['password'] === $password) {
                    $response = ["id" => $result['id'], "status" => "ALL OK"];
                } else {
                    $response = ["id" => -1, "status" => "Wrong password"];
                }
            } else {
                $response = ["id" => -1, "status" => "USER NAME DOES NOT EXIST"];
            }
        } catch (PDOException $e) {
            error_log("Error verifying user '{$username}': " . $e->getMessage());
            $response = ["id" => -1, "status" => "Database error during verification."];
        }
        return $response;
    }

    /**
     * Retrieves courses assigned to a specific faculty in a given session.
     *
     * @param Database $dbo The Database object.
     * @param int $sessionId The ID of the session.
     * @param int $facultyId The ID of the faculty.
     * @return array An array of course details (id, code, title), or an empty array on failure.
     */
    public function getCoursesInASession(Database $dbo, int $sessionId, int $facultyId): array {
        $courses = [];
        $sql = "SELECT cd.id, cd.code, cd.title 
                FROM course_allotment AS ca
                JOIN course_details AS cd ON ca.course_id = cd.id
                WHERE ca.faculty_id = :faculty_id AND ca.session_id = :session_id
                ORDER BY cd.code ASC";
        $stmt = $dbo->prepare($sql);
        try {
            $stmt->execute([
                ":faculty_id" => $facultyId,
                ":session_id" => $sessionId
            ]);
            $courses = $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching courses for faculty {$facultyId} in session {$sessionId}: " . $e->getMessage());
        }
        return $courses;
    }

    // Removed the hashPassword method since it's no longer needed
}
?>
