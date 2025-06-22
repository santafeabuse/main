<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Logout user
logout_user();

// Redirect to home page
redirect('../index.php');
?>