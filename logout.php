<?php
require_once 'includes/config.php';

// پاک کردن تمام اطلاعات جلسه
session_unset();
session_destroy();

// هدایت به صفحه ورود
header('Location: login.php');
exit;