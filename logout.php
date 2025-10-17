<?php
include __DIR__ . '/includes/db.php';
include __DIR__ . '/includes/auth.php';
authLogout();
header('Location: index.php');
exit;

