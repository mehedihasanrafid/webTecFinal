<?php
// student_ms/database/attendanceDetails.php

// Ensure Database class is available
if (!class_exists('Database')) {
    require_once __DIR__ . "/database.php";
}

class AttendanceDetails { // Renamed to AttendanceDetails for consistency
    /**
     * Saves or updates attendance for a student on a specific date.
     *
     * @param Database $dbo The Database object.
     * @param int $sessionId The ID of the session.
     * @param int $courseId The ID of the course.
     * @param int $facultyId The ID of the faculty.
     * @param int $studentId The ID of the student.
     * @param string $onDate The date of attendance (YYYY-MM-DD).
     * @param string $status The attendance status ('YES' or 'NO').
     * @return bool True on success, false on failure.
     */
    public function saveAttendance(Database $dbo, int $sessionId, int $courseId, int $facultyId, int $studentId, string $onDate, string $status): bool {
        // First, try to update if an entry already exists
        $updateSql = "UPDATE attendance_details 
                      SET status = :status
                      WHERE session_id = :session_id 
                      AND course_id = :course_id 
                      AND faculty_id = :faculty_id
                      AND student_id = :student_id 
                      AND on_date = :on_date";
        $updateStmt = $dbo->prepare($updateSql);

        try {
            $updateStmt->execute([
                ":status" => $status,
                ":session_id" => $sessionId,
                ":course_id" => $courseId,
                ":faculty_id" => $facultyId,
                ":student_id" => $studentId,
                ":on_date" => $onDate
            ]);

            // If a row was updated, return true
            if ($updateStmt->rowCount() > 0) {
                return true;
            }

            // If no row was updated, it means the entry doesn't exist, so insert it
            $insertSql = "INSERT INTO attendance_details
                          (session_id, course_id, faculty_id, student_id, on_date, status)
                          VALUES
                          (:session_id, :course_id, :faculty_id, :student_id, :on_date, :status)";
            $insertStmt = $dbo->prepare($insertSql);
            $insertStmt->execute([
                ":session_id" => $sessionId,
                ":course_id" => $courseId,
                ":faculty_id" => $facultyId,
                ":student_id" => $studentId,
                ":on_date" => $onDate,
                ":status" => $status
            ]);
            return true; // Insert successful
        } catch (PDOException $e) {
            error_log("Error saving attendance for student {$studentId} on {$onDate}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves the list of students marked as 'YES' (present) for a specific class by a faculty on a given date.
     *
     * @param Database $dbo The Database object.
     * @param int $sessionId The ID of the session.
     * @param int $courseId The ID of the course.
     * @param int $facultyId The ID of the faculty.
     * @param string $onDate The date of attendance (YYYY-MM-DD).
     * @return array An array of student IDs who were present, or an empty array on failure.
     */
    public function getPresentListOfAClassByAFacOnADate(Database $dbo, int $sessionId, int $courseId, int $facultyId, string $onDate): array {
        $presentStudents = [];
        $sql = "SELECT student_id 
                FROM attendance_details 
                WHERE session_id = :session_id 
                AND course_id = :course_id
                AND faculty_id = :faculty_id 
                AND on_date = :on_date
                AND status = 'YES'";
        $stmt = $dbo->prepare($sql);
        try {
            $stmt->execute([
                ":session_id" => $sessionId,
                ":course_id" => $courseId,
                ":faculty_id" => $facultyId,
                ":on_date" => $onDate
            ]);
            $presentStudents = $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching present list for course {$courseId} on {$onDate}: " . $e->getMessage());
        }
        return $presentStudents;
    }

    /**
     * Generates an attendance report for a specific course and faculty in a given session.
     *
     * @param Database $dbo The Database object.
     * @param int $sessionId The ID of the session.
     * @param int $courseId The ID of the course.
     * @param int $facultyId The ID of the faculty.
     * @return array A structured array containing report data, or an empty array on failure.
     */
    public function getAttenDanceReport(Database $dbo, int $sessionId, int $courseId, int $facultyId): array {
        $report = [];

        // Fetch session details
        $sessionName = 'N/A';
        $sqlSession = "SELECT year, term FROM session_details WHERE id = :id";
        $stmtSession = $dbo->prepare($sqlSession);
        try {
            $stmtSession->execute([":id" => $sessionId]);
            $sd = $stmtSession->fetch();
            if ($sd) {
                $sessionName = $sd['year'] . " " . $sd['term'];
            }
        } catch (PDOException $e) {
            error_log("Error fetching session name for report: " . $e->getMessage());
        }

        // Fetch faculty details
        $facName = 'N/A';
        $sqlFaculty = "SELECT name FROM faculty_details WHERE id = :id";
        $stmtFaculty = $dbo->prepare($sqlFaculty);
        try {
            $stmtFaculty->execute([":id" => $facultyId]);
            $fd = $stmtFaculty->fetch();
            if ($fd) {
                $facName = $fd['name'];
            }
        } catch (PDOException $e) {
            error_log("Error fetching faculty name for report: " . $e->getMessage());
        }

        // Fetch course details
        $courseName = 'N/A';
        $sqlCourse = "SELECT code, title FROM course_details WHERE id = :id";
        $stmtCourse = $dbo->prepare($sqlCourse);
        try {
            $stmtCourse->execute([":id" => $courseId]);
            $cd = $stmtCourse->fetch();
            if ($cd) {
                $courseName = $cd['code'] . "-" . $cd['title'];
            }
        } catch (PDOException $e) {
            error_log("Error fetching course name for report: " . $e->getMessage());
        }

        // Add header information to the report
        // These will be handled as separate rows in the CSV, not as part of the main data table
        $report[] = ["type" => "header", "label" => "Session:", "value" => $sessionName];
        $report[] = ["type" => "header", "label" => "Course:", "value" => $courseName];
        $report[] = ["type" => "header", "label" => "Faculty:", "value" => $facName];

        // Get total number of classes conducted by the faculty for this course in this session
        $totalClasses = 0;
        $startDate = '';
        $endDate = '';
        $sqlTotalClasses = "SELECT DISTINCT on_date 
                            FROM attendance_details 
                            WHERE session_id = :session_id 
                            AND course_id = :course_id 
                            AND faculty_id = :faculty_id
                            ORDER BY on_date ASC";
        $stmtTotalClasses = $dbo->prepare($sqlTotalClasses);
        try {
            $stmtTotalClasses->execute([
                ":session_id" => $sessionId,
                ":course_id" => $courseId,
                ":faculty_id" => $facultyId
            ]);
            $dates = $stmtTotalClasses->fetchAll(PDO::FETCH_COLUMN); // Fetch only the 'on_date' column
            $totalClasses = count($dates);
            if ($totalClasses > 0) {
                $startDate = $dates[0];
                $endDate = $dates[$totalClasses - 1];
            }
        } catch (PDOException $e) {
            error_log("Error fetching total classes for report: " . $e->getMessage());
        }
        
        $report[] = ["type" => "header", "label" => "Total Classes Conducted:", "value" => $totalClasses];
        $report[] = ["type" => "header", "label" => "Period Start Date:", "value" => $startDate];
        $report[] = ["type" => "header", "label" => "Period End Date:", "value" => $endDate];

        // Get attended classes for each registered student
        $sqlAttendedClasses = "SELECT 
                                rsd.id AS student_id, 
                                rsd.roll_no, 
                                rsd.name,
                                COUNT(ad.on_date) AS attended_classes
                               FROM 
                                (
                                   SELECT sd.id, sd.roll_no, sd.name, crd.session_id, crd.course_id 
                                   FROM student_details AS sd
                                   JOIN course_registration AS crd ON sd.id = crd.student_id
                                   WHERE crd.session_id = :session_id AND crd.course_id = :course_id
                                ) AS rsd
                                LEFT JOIN attendance_details AS ad 
                                ON rsd.id = ad.student_id 
                                AND rsd.session_id = ad.session_id 
                                AND rsd.course_id = ad.course_id
                                AND ad.faculty_id = :faculty_id
                                AND ad.status = 'YES'
                                GROUP BY rsd.id, rsd.roll_no, rsd.name
                                ORDER BY rsd.roll_no ASC";
        $stmtAttendedClasses = $dbo->prepare($sqlAttendedClasses);
        $studentAttendanceData = [];
        try {
            $stmtAttendedClasses->execute([
                ":session_id" => $sessionId,
                ":course_id" => $courseId,
                ":faculty_id" => $facultyId
            ]);
            $studentAttendanceData = $stmtAttendedClasses->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching student attendance data for report: " . $e->getMessage());
        }

        // Compute percentage and format for CSV
        foreach ($studentAttendanceData as $student) {
            $percent = 0.00;
            if ($totalClasses > 0) {
                $percent = round(($student['attended_classes'] / $totalClasses) * 100.0, 2);
            }
            $report[] = [
                "type" => "data",
                "slno" => $student['student_id'], // Using student_id as slno for unique identification in CSV
                "rollno" => $student['roll_no'],
                "name" => $student['name'],
                "attended" => $student['attended_classes'],
                "percent" => $percent
            ];
        }
        
        return $report;
    }
}
?>
