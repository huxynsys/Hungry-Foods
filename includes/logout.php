<?php
session_start();
session_destroy();
$base = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/');
header("Location: " . $base . "/index.php");
exit;
?>
