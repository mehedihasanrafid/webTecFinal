<?php
//ajaxhandler/loginAjax.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/student_ms/database/database.php";
require_once $path . "/student_ms/database/facultyDetails.php";

$action = $_REQUEST["action"] ?? '';

if (!empty($action) && $action === "verifyUser") {
    $un = $_POST["user_name"] ?? '';
    $pw = $_POST["password"] ?? '';

    $dbo = new Database();
    $fdo = new faculty_details();
    $rv = $fdo->verifyUser($dbo, $un, $pw);

    if ($rv['status'] === "ALL OK") {
        session_start();
        $_SESSION['current_user'] = $rv['id'];
        $_SESSION['is_admin'] = $rv['is_admin'];
    }

    // Optional delay for debugging/demo
    // for ($i = 0; $i < 1000; $i++) for ($j = 0; $j < 200; $j++) {}

    header('Content-Type: application/json');
    echo json_encode($rv);
    exit;
}
?>
