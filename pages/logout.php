<?php
require_once "../controllers/authController.php";

// Call the logout function
User::logout();

// Redirect to login page
header("Location: login.php");
exit();
?>