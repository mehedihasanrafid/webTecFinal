<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/student_ms/database/database.php";


function clearTable($dbo, $tabName)
{
    $sql = "DELETE FROM `$tabName`";
    try {
        $dbo->conn->exec($sql);
    } catch (PDOException $e) {
        echo "<br>Failed to clear $tabName: " . $e->getMessage();
    }
}


$dbo = new Database();

$tables = [
    "CREATE TABLE IF NOT EXISTS student_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        roll_no VARCHAR(20) UNIQUE,
        name VARCHAR(50)
    )",
    "CREATE TABLE IF NOT EXISTS course_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(20) UNIQUE,
        title VARCHAR(50),
        credit INT
    )",
    "CREATE TABLE IF NOT EXISTS faculty_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_name VARCHAR(20) UNIQUE,
        name VARCHAR(100),
        password VARCHAR(50)
    )",
    "CREATE TABLE IF NOT EXISTS session_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        year INT,
        term VARCHAR(50),
        UNIQUE (year, term)
    )",
    "CREATE TABLE IF NOT EXISTS course_registration (
        student_id INT,
        course_id INT,
        session_id INT,
        PRIMARY KEY (student_id, course_id, session_id)
    )",
    "CREATE TABLE IF NOT EXISTS course_allotment (
        faculty_id INT,
        course_id INT,
        session_id INT,
        PRIMARY KEY (faculty_id, course_id, session_id)
    )",
    "CREATE TABLE IF NOT EXISTS attendance_details (
        faculty_id INT,
        course_id INT,
        session_id INT,
        student_id INT,
        on_date DATE,
        status VARCHAR(10),
        PRIMARY KEY (faculty_id, course_id, session_id, student_id, on_date)
    )"
];

foreach ($tables as $query) {
    try {
        $dbo->conn->exec($query);
        echo "<br>Table created.";
    } catch (PDOException $e) {
        echo "<br>Table creation failed: " . $e->getMessage();
    }
}

// === CLEAR EXISTING DATA ===
$tablesToClear = [
    "attendance_details",
    "course_registration",
    "course_allotment",
    "session_details",
    "faculty_details",
    "course_details",
    "student_details"
];
foreach ($tablesToClear as $table) {
    clearTable($dbo, $table);
}

// === INSERT STUDENTS (Bangladeshi names) ===
$students = [
    "CSBD21001" => "Mehedi Hasan",
    "CSBD21002" => "Ayesha Siddiqua",
    "CSBD21003" => "Sabbir Hossain",
    "CSBD21004" => "Nusrat Jahan",
    "CSBD21005" => "Tahmid Rahman",
    "CSBD21006" => "Sadia Afrin",
    "CSBD21007" => "Hasibul Islam",
    "CSBD21008" => "Farzana Akter",
    "CSBD21009" => "Junaid Alam",
    "CSBD21010" => "Tanjila Haque",
    "CSBD21011" => "Rashed Khan",
    "CSBD21012" => "Maliha Tasnim"
];
$s = $dbo->conn->prepare("INSERT INTO student_details (roll_no, name) VALUES (?, ?)");
foreach ($students as $roll => $name) {
    $s->execute([$roll, $name]);
}


$faculties = [
    ["nurul", "Nurul Islam"],
    ["mahmud", "Mahmud Hasan"],
    ["sultana", "Sultana Jahan"],
    ["tariq", "Tariq Aziz"],
    ["shirin", "Shirin Akter"],
    ["kamal", "Kamal Hossain"]
];
$s = $dbo->conn->prepare("INSERT INTO faculty_details (user_name, password, name) VALUES (?, '123', ?)");
foreach ($faculties as $f) {
    $s->execute([$f[0], $f[1]]);
}


$sessions = [
    [2024, "SPRING SEMESTER"],
    [2024, "AUTUMN SEMESTER"]
];
$s = $dbo->conn->prepare("INSERT INTO session_details (year, term) VALUES (?, ?)");
foreach ($sessions as $sess) {
    $s->execute($sess);
}


$courses = [
    ["CSE101", "Structured Programming", 3],
    ["CSE203", "Data Structures", 3],
    ["CSE305", "Database Systems", 3],
    ["CSE307", "Web Technologies", 2],
    ["CSE409", "Machine Learning", 3],
    ["CSE501", "Computer Networks", 3]
];
$s = $dbo->conn->prepare("INSERT INTO course_details (code, title, credit) VALUES (?, ?, ?)");
foreach ($courses as $c) {
    $s->execute($c);
}


$studentsCount = count($students);
$s = $dbo->conn->prepare("INSERT INTO course_registration (student_id, course_id, session_id) VALUES (?, ?, ?)");
for ($studentId = 1; $studentId <= $studentsCount; $studentId++) {
    for ($sessId = 1; $sessId <= 2; $sessId++) {
        $selected = [];
        while (count($selected) < 2) {
            $cid = rand(1, 6);
            if (!in_array($cid, $selected)) {
                $selected[] = $cid;
                try {
                    $s->execute([$studentId, $cid, $sessId]);
                } catch (PDOException $e) {}
            }
        }
    }
}

$facultiesCount = count($faculties);
$s = $dbo->conn->prepare("INSERT INTO course_allotment (faculty_id, course_id, session_id) VALUES (?, ?, ?)");
for ($facId = 1; $facId <= $facultiesCount; $facId++) {
    for ($sessId = 1; $sessId <= 2; $sessId++) {
        $selected = [];
        while (count($selected) < 2) {
            $cid = rand(1, 6);
            if (!in_array($cid, $selected)) {
                $selected[] = $cid;
                try {
                    $s->execute([$facId, $cid, $sessId]);
                } catch (PDOException $e) {}
            }
        }
    }
}

echo "<br><strong>Setup completed successfully.</strong>";
?>
