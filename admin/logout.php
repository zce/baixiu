<?php
/**
 * 退出登录
 */

session_start();

// 删除登录状态
unset($_SESSION['current_logged_in_user_id']);

// 跳转
header('Location: /admin/login.php');
