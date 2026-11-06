<?php
require_once __DIR__ . '/includes/auth.php';
if (is_post()) { csrf_check(); logout_user(); }
redirect('login.php');
