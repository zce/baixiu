<?php
/**
 * 修改评论状态
 * POST 方式请求
 * - id 参数在 URL 中
 * - status 参数在 form-data 中
 * 两种参数混着用
 */

require '../functions.php';

// 设置响应类型为 JSON
header('Content-Type: application/json');

if (empty($_GET['id']) || empty($_POST['status'])) {
  // 缺少必要参数
  exit(json_encode(array(
    'success' => false,
    'message' => '缺少必要参数'
  )));
}

// 拼接 SQL 并执行
$affected_rows = xiu_execute(sprintf("update comments set status = '%s' where id in (%s)", $_POST['status'], $_GET['id']));

// 输出结果
echo json_encode(array(
  'success' => $affected_rows > 0
));
