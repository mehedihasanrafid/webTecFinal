<?php
// student_ms/marks.php
session_start(); // Start session at the very beginning

// Redirect if not logged in
if (!isset($_SESSION["current_user"])) {
    header("Location: login.php"); // Use relative path
    exit();
}

$facultyId = htmlspecialchars($_SESSION["current_user"]); // Sanitize output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/marks.css"> <!-- Specific CSS for marks -->
    <link rel="stylesheet" href="css/loader.css"> <!-- Include loader CSS -->
    <title>Marks Entry</title>
</head>
<body>
    <div class="page">
        <div class="header-area">
            <div class="logo-area"> <h2 class="logo">MARKS ENTRY</h2></div>
            <div class="logout-area">
                <button id="btAttendance">ATTENDANCE</button>
                <button class="btnlogout" id="btnLogout">LOGOUT</button>
            </div>
        </div>
        <div class="session-area">
            <div class="label-area"><label>SESSION</label></div>
            <div class="dropdown-area">
                <select class="ddlclass" id="ddlclass">
                    <option value="-1">SELECT ONE</option>
                </select>
            </div>
        </div>

        <div class="classlist-area" id="classlistarea">
            <!-- Course cards will be loaded here -->
        </div>

        <div class="classdetails-area" id="classdetailsarea">
            <!-- Class details will be loaded here -->
        </div>
        
        <div class="studentlist-area" id="studentlistarea">
            <!-- Student list and marks input fields will be loaded here -->
        </div>
        
        <input type="hidden" id="hiddenFacId" value="<?php echo $facultyId; ?>">
        <input type="hidden" id="hiddenSelectedCourseID" value="-1">
    </div>

    <div class="lockscreen" id="lockscreen">
        <div class="spinner" id="spinner"></div>
        <label class="lblwait topmargin" id="lblwait">PLEASE WAIT</label>
    </div>
    
    <script src="js/utils.js"></script> <!-- NEW: Shared utility functions -->
    <script src="js/marks.js"></script>
</body>
</html>
