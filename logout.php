<?php
session_start();
session_destroy();

// Cookie löschen
setcookie('remember_token', '', time() - 3600, "/");

header('Location: login.php');
exit;

?>