<?php
/**
 * 删除用户
 */

require '../functions.php';

if (!empty($_GET['id'])) {
  // 拼接 SQL 并执行
  xiu_execute(sprintf('delete from users where id in (%s)', $_GET['id']));
}

// 获取删除后跳转到的目标链接，优先跳转到来源页面，否则默认跳转到列表页
$target = empty($_SERVER['HTTP_REFERER']) ? '/admin/users.php' : $_SERVER['HTTP_REFERER'];
header('Location: ' . $target);
