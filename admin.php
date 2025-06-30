<?php
session_start();
if (!isset($_SESSION['current_user']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
    <script src="js/jquery.js"></script>
    <script src="js/admin.js"></script>
</head>
<body>
    <h1>Admin Dashboard</h1>

    
    <button id="btnLogout">Logout</button>
    <button id="btnAttendance">Academic</button>

    <hr />

    
    <h2>Add New Session</h2>
    <form id="formAddSession">
        <label for="sessionYear">Year:</label>
        <select id="sessionYear" name="year" required>
            <?php
            $currentYear = date("Y");
            for ($y = $currentYear; $y <= $currentYear + 5; $y++) {
                echo "<option value='$y'>$y</option>";
            }
            ?>
        </select>

        <label for="sessionTerm">Term:</label>
        <select id="sessionTerm" name="term" required>
            <option value="Fall">Fall</option>
            <option value="Summer">Summer</option>
            <option value="Spring">Spring</option>
        </select>

        <button type="submit">Add Session</button>
    </form>
    <div id="sessionMessage"></div>

    <hr />

    
    <h2>Add New Class (Course)</h2>
    <form id="formAddClass">
        <label for="classYear">Year:</label>
        <select id="classYear" name="year" required></select>

        <label for="classTerm">Term:</label>
        <select id="classTerm" name="term" required></select>

        <label for="courseCode">Course Code:</label>
        <input type="text" id="courseCode" name="code" required />

        <label for="courseTitle">Course Title:</label>
        <input type="text" id="courseTitle" name="title" required />

        <label for="courseCredit">Credit:</label>
        <input type="number" id="courseCredit" name="credit" min="1" max="10" required />

        <button type="submit">Add Class</button>
    </form>
    <div id="classMessage"></div>

    <hr />

    
    <h2>Add New Faculty</h2>
    <form id="formAddFaculty">
        <label for="facultyUsername">Username:</label>
        <input type="text" id="facultyUsername" name="user_name" required />

        <label for="facultyName">Name:</label>
        <input type="text" id="facultyName" name="name" required />

        <label for="facultyPassword">Password:</label>
        <input type="password" id="facultyPassword" name="password" required />

        <label for="facultyIsAdmin">Is Admin:</label>
        <input type="checkbox" id="facultyIsAdmin" name="is_admin" value="1" />

        <button type="submit">Add Faculty</button>
    </form>
    <div id="facultyMessage"></div>

    <hr />

    
    <h2>Assign Classes to Faculty</h2>
    <form id="formAssignClass">
        <label for="assignSession">Session:</label>
        <select id="assignSession" name="session_id" required></select>

        <label for="assignClass">Class:</label>
        <select id="assignClass" name="course_id" required></select>

        <label for="assignFaculty">Faculty:</label>
        <select id="assignFaculty" name="faculty_id" required></select>

        <button type="submit">Assign Class</button>
    </form>
    <div id="assignMessage"></div>

</body>
</html>
