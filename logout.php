<?php
require_once __DIR__ . '/includes/Auth.php';
Auth::init();
Auth::logout();
header('Location: /login.php');
exit;
