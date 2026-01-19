<?php
// www/logout.php
session_start();
session_destroy(); // Șterge toate datele sesiunii
header("Location: login.html"); // Trimite înapoi la login
exit;
?>