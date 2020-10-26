<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
}
else {
	unset($_SESSION['username'], $_SESSION['userid']);
	session_destroy();

    header("Location: index.php");
    exit;
}
?>