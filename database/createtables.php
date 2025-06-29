<?php
// student_ms/createtables.php

// Ensure Database class is available
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/student_ms/database/database.php";

// Function to safely clear table data
function clearTable(Database $dbo, string $tabName): void {
    // Table names cannot be parameterized, so validate input carefully
    // In a real application, you'd have a whitelist of allowed table names.
    $allowedTables = [
        "student_details", "course_details", "faculty_details", "session_details",
        "course_registration", "course_allotment", "attendance_details", "marks_details"
    ];

    if (!in_array($tabName, $allowedTables)) {
        error_log("Attempted to clear unauthorized table: " . $tabName);
        return;
    }

    $sql = "DELETE FROM " . $tabName; // Concatenate table name directly after validation
    $stmt = $dbo->prepare($sql);
    try {
        $stmt->execute();
        echo "<br>Table '{$tabName}' cleared.";
    } catch (PDOException $e) {
        error_log("Error clearing table '{$tabName}': " . $e->getMessage());
        echo "<br>Error clearing table '{$tabName}'.";
    }
}

// Get the single instance of the Database class
$dbo = Database::getInstance();

echo "<h2>Database Setup and Data Seeding</h2>";

// --- Create Tables ---

// student_details
$sql = "CREATE TABLE IF NOT EXISTS student_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_no VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(50) NOT NULL
)";
try {
    $dbo->prepare($sql)->execute();
    echo "<br>Table 'student_details' created or already exists.";
} catch (PDOException $e) {
    error_log("Error creating student_details table: " . $e->getMessage());
    echo "<br>Error creating 'student_details' table.";
}

// course_details
$sql = "CREATE TABLE IF NOT EXISTS course_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(50) NOT NULL,
    credit INT NOT NULL
)";
try {
    $dbo->prepare($sql)->execute();
    echo "<br>Table 'course_details' created or already exists.";
} catch (PDOException $e) {
    error_log("Error creating course_details table: " . $e->getMessage());
    echo "<br>Error creating 'course_details' table.";
}

// faculty_details
$sql = "CREATE TABLE IF NOT EXISTS faculty_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    password VARCHAR(50) NOT NULL -- Changed to VARCHAR(50) for plain text
)";
try {
    $dbo->prepare($sql)->execute();
    echo "<br>Table 'faculty_details' created or already exists.";
} catch (PDOException $e) {
    error_log("Error creating faculty_details table: " . $e->getMessage());
    echo "<br>Error creating 'faculty_details' table.";
}

// session_details
$sql = "CREATE TABLE IF NOT EXISTS session_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL,
    term VARCHAR(50) NOT NULL,
    UNIQUE (year, term)
)";
try {
    $dbo->prepare($sql)->execute();
    echo "<br>Table 'session_details' created or already exists.";
} catch (PDOException $e) {
    error_log("Error creating session_details table: " . $e->getMessage());
    echo "<br>Error creating 'session_details' table.";
}

// course_registration
$sql = "CREATE TABLE IF NOT EXISTS course_registration (
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    session_id INT NOT NULL,
    PRIMARY KEY (student_id, course_id, session_id),
    FOREIGN KEY (student_id) REFERENCES student_details(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES course_details(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES session_details(id) ON DELETE CASCADE
)";
try {
    $dbo->prepare($sql)->execute();
    echo "<br>Table 'course_registration' created or already exists.";
} catch (PDOException $e) {
    error_log("Error creating course_registration table: " . $e->getMessage());
    echo "<br>Error creating 'course_registration' table.";
}

// course_allotment
$sql = "CREATE TABLE IF NOT EXISTS course_allotment (
    faculty_id INT NOT NULL,
    course_id INT NOT NULL,
    session_id INT NOT NULL,
    PRIMARY KEY (faculty_id, course_id, session_id),
    FOREIGN KEY (faculty_id) REFERENCES faculty_details(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES course_details(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES session_details(id) ON DELETE CASCADE
)";
try {
    $dbo->prepare($sql)->execute();
    echo "<br>Table 'course_allotment' created or already exists.";
} catch (PDOException $e) {
    error_log("Error creating course_allotment table: " . $e->getMessage());
    echo "<br>Error creating 'course_allotment' table.";
}

// attendance_details
$sql = "CREATE TABLE IF NOT EXISTS attendance_details (
    faculty_id INT NOT NULL,
    course_id INT NOT NULL,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    on_date DATE NOT NULL,
    status VARCHAR(10) NOT NULL,
    PRIMARY KEY (faculty_id, course_id, session_id, student_id, on_date),
    FOREIGN KEY (faculty_id) REFERENCES faculty_details(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES course_details(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES session_details(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_details(id) ON DELETE CASCADE
)";
try {
    $dbo->prepare($sql)->execute();
    echo "<br>Table 'attendance_details' created or already exists.";
} catch (PDOException $e) {
    error_log("Error creating attendance_details table: " . $e->getMessage());
    echo "<br>Error creating 'attendance_details' table.";
}

// marks_details
$sql = "CREATE TABLE IF NOT EXISTS marks_details (
    faculty_id INT NOT NULL,
    course_id INT NOT NULL,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    marks FLOAT NOT NULL,
    PRIMARY KEY (faculty_id, course_id, session_id, student_id),
    FOREIGN KEY (faculty_id) REFERENCES faculty_details(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES course_details(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES session_details(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_details(id) ON DELETE CASCADE
)";
try {
    $dbo->prepare($sql)->execute();
    echo "<br>Table 'marks_details' created or already exists.";
} catch (PDOException $e) {
    error_log("Error creating marks_details table: " . $e->getMessage());
    echo "<br>Error creating 'marks_details' table.";
}

echo "<h3>Seeding Data...</h3>";

// --- Insert Data ---

// student_details
clearTable($dbo, "student_details");
$sql = "INSERT INTO student_details (id, roll_no, name) VALUES
(1,'CSB21001','Emily Johnson'), (2,'CSB21002','Michael Smith'),
(3,'CSB21003','Sarah Martinez'), (4,'CSB21004','David Brown'),
(5,'CSB21005','Olivia Williams'), (6,'CSB21006','Christopher Davis'),
(7,'CSB21007','Sophia Wilson'), (8,'CSB21008','Ethan Anderson'),
(9,'CSB21009','Emma Miller'), (10,'CSB21010','James Jones'),
(11,'CSB21011','Ava Taylor'), (12,'CSB21012','Daniel Thomas'),
(13,'CSM21001','Mia Garcia'), (14,'CSM21002','William White'),
(15,'CSM21003','Chloe Martin'), (16,'CSM21004','Benjamin Clark'),
(17,'CSM21005','Isabella Hall'), (18,'CSM21006','Alexander Lee'),
(19,'CSM21007','Grace Lewis'), (20,'CSM21008','Samuel Turner'),
(21,'CSM21009','Natalie Harris'), (22,'CSM21010','Caleb Baker'),
(23,'CSM21011','Lily Reed'), (24,'CSM21012','Logan Murphy')
ON DUPLICATE KEY UPDATE roll_no=VALUES(roll_no), name=VALUES(name)"; // Handle duplicates
try {
    $dbo->prepare($sql)->execute();
    echo "<br>Student data inserted.";
} catch (PDOException $e) {
    error_log("Error inserting student data: " . $e->getMessage());
    echo "<br>Error inserting student data.";
}

// faculty_details (with plain text passwords)
clearTable($dbo, "faculty_details");
$sql = "INSERT INTO faculty_details (id, user_name, password, name) VALUES
(1,'rcb','123','Ram Charan Baishya'),
(2,'arindam','123','Arindam Karmakar'),
(3,'pal','123','Pallabi'),
(4,'anuj','123','Anuj Agarwal'),
(5,'mriganka','123','Mriganka Sekhar'),
(6,'manooj','123','Manooj Hazarika')
ON DUPLICATE KEY UPDATE user_name=VALUES(user_name), password=VALUES(password), name=VALUES(name)";

try {
    $dbo->prepare($sql)->execute();
    echo "<br>Faculty data inserted (passwords in plain text).";
} catch (PDOException $e) {
    error_log("Error inserting faculty data: " . $e->getMessage());
    echo "<br>Error inserting faculty data.";
}

// session_details
clearTable($dbo, "session_details");
$sql = "INSERT INTO session_details (id, year, term) VALUES
(1,2023,'SPRING SEMESTER'),
(2,2023,'AUTUMN SEMESTER')
ON DUPLICATE KEY UPDATE year=VALUES(year), term=VALUES(term)";
try {
    $dbo->prepare($sql)->execute();
    echo "<br>Session data inserted.";
} catch (PDOException $e) {
    error_log("Error inserting session data: " . $e->getMessage());
    echo "<br>Error inserting session data.";
}

// course_details
clearTable($dbo, "course_details");
$sql = "INSERT INTO course_details (id, title, code, credit) VALUES
(1,'Database management system lab','CO321',2),
(2,'Pattern Recognition','CO215',3),
(3,'Data Mining & Data Warehousing','CS112',4),
(4,'ARTIFICIAL INTELLIGENCE','CS670',4),
(5,'THEORY OF COMPUTATION ','CO432',3),
(6,'DEMYSTIFYING NETWORKING ','CS673',1)
ON DUPLICATE KEY UPDATE title=VALUES(title), code=VALUES(code), credit=VALUES(credit)";
try {
    $dbo->prepare($sql)->execute();
    echo "<br>Course data inserted.";
} catch (PDOException $e) {
    error_log("Error inserting course data: " . $e->getMessage());
    echo "<br>Error inserting course data.";
}

// course_registration
clearTable($dbo, "course_registration");
$sql = "INSERT INTO course_registration (student_id, course_id, session_id) VALUES (:sid, :cid, :sessid)";
$stmt = $dbo->prepare($sql);

for ($i = 1; $i <= 24; $i++) { // Iterate over all 24 students
  for ($j = 0; $j < 3; $j++) { // Each student registers for max 3 random courses
    $cid = rand(1, 6);
    // Session 1
    try {
      $stmt->execute([":sid" => $i, ":cid" => $cid, ":sessid" => 1]);
    } catch (PDOException $pe) {
        // Duplicate entry, ignore or log
    }

    // Session 2
    $cid = rand(1, 6); // New random course for session 2
    try {
      $stmt->execute([":sid" => $i, ":cid" => $cid, ":sessid" => 2]);
    } catch (PDOException $pe) {
        // Duplicate entry, ignore or log
    }
  }
}
echo "<br>Course registration data seeded.";

// course_allotment
clearTable($dbo, "course_allotment");
$sql = "INSERT INTO course_allotment (faculty_id, course_id, session_id) VALUES (:fid, :cid, :sessid)";
$stmt = $dbo->prepare($sql);

for ($i = 1; $i <= 6; $i++) { // Iterate over all 6 teachers
  for ($j = 0; $j < 2; $j++) { // Each teacher is allotted max 2 random courses
    $cid = rand(1, 6);
    // Session 1
    try {
      $stmt->execute([":fid" => $i, ":cid" => $cid, ":sessid" => 1]);
    } catch (PDOException $pe) {
        // Duplicate entry, ignore or log
    }

    // Session 2
    $cid = rand(1, 6); // New random course for session 2
    try {
      $stmt->execute([":fid" => $i, ":cid" => $cid, ":sessid" => 2]);
    } catch (PDOException $pe) {
        // Duplicate entry, ignore or log
    }
  }
}
echo "<br>Course allotment data seeded.";

echo "<h3>Database setup complete!</h3>";
?>
