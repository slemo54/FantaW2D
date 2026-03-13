<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

logout();
set_notification('You have been logged out successfully');
header('Location: index.php');
exit;