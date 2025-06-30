<?php
// ajaxhandler/logoutAjax.php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

$rv = [];
echo json_encode($rv);
?>
