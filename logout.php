<?php
session_start();

unset($_SESSION['user_id']);
unset($_SESSION['user_pw']);
unset($_SESSION['user_alias']);

session_destroy();
?>

