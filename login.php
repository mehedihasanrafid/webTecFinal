<?php
// student_ms/login.php
session_start(); // Start session at the very beginning

// Redirect if already logged in
if (isset($_SESSION["current_user"])) {
    header("Location: attendance.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/loader.css">
    <title>Login - Student Management System</title>
</head>
<body>
    <div class="loginform">
        <div class="inputgroup topmarginlarge">
            <input type="text" id="txtUsername" required autocomplete="username">
            <label for="txtUsername" id="lblUsername">USER NAME</label>
        </div>

        <div class="inputgroup topmarginlarge">
            <input type="password" id="txtPassword" required autocomplete="current-password">
            <label for="txtPassword" id="lblPassword">PASSWORD</label>
        </div>
        <div class="divcallforaction topmarginlarge">
            <button class="btnlogin inactivecolor" id="btnLogin">LOGIN</button>
        </div>  
        
        <div class="diverror topmarginlarge" id="diverror">
            <label class="errormessage" id="errormessage">ERROR GOES HERE</label>
        </div>
    </div>
    <div class="lockscreen" id="lockscreen">
        <div class="spinner" id="spinner"></div>
        <label class="lblwait topmargin" id="lblwait">PLEASE WAIT</label>
    </div>
       
    <script src="js/utils.js"></script> <!-- NEW: Shared utility functions -->
    <script src="js/login.js"></script>
</body>
</html>
