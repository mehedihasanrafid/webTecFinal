<?php
// student_ms/database/sessionDetails.php

// Ensure Database class is available
if (!class_exists('Database')) {
    require_once __DIR__ . "/database.php";
}

class SessionDetails {
    /**
     * Retrieves all sessions from the database.
     *
     * @param Database $dbo The Database object.
     * @return array An array of session details, or an empty array on failure.
     */
    public function getSessions(Database $dbo): array {
        $sessions = [];
        $sql = "SELECT id, year, term FROM session_details ORDER BY year DESC, term ASC";
        $stmt = $dbo->prepare($sql);
        try {
            $stmt->execute();
            $sessions = $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching sessions: " . $e->getMessage());
            // In a production environment, you might throw a more generic exception
            // or return a specific error code.
        }
        return $sessions;
    }
}
?>
