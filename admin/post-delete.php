<?php
/**
 * 删除文章
 */

require '../functions.php';

if (!empty($_GET['id'])) {
  // 拼接 SQL 并执行
  xiu_execute(sprintf('delete from posts where id = %d', $_GET['id']));
}
