<?php
/**
 * 分页返回评论数据接口（JSON）
 */

require '../functions.php';

// 处理分页参数
// ========================================

// 页码
$page = isset($_GET['p']) && is_numeric($_GET['p']) ? intval($_GET['p']) : 1;

// 页大小
$size = isset($_GET['s']) && is_numeric($_GET['s']) ? intval($_GET['s']) : 20;

// 检查页码最小值
if ($page <= 0) {
  header('Location: /admin/comment-list.php?p=1&s=' . $size);
  exit;
}

// 查询总条数
$total_count = intval(xiu_query('select count(1) from comments
inner join posts on comments.post_id = posts.id')[0][0]);

// 计算总页数
$total_pages = ceil($total_count / $size);

// 检查页码最大值
if ($page > $total_pages) {
  // 跳转到最后一页
  header('Location: /admin/comment-list.php?p=' . $total_pages . '&s=' .$size);
  exit;
}

// 查询数据
// ========================================

// 分页查询评论数据
$comments = xiu_query(sprintf('select
  comments.*, posts.title as post_title
from comments
inner join posts on comments.post_id = posts.id
order by comments.created desc
limit %d, %d', ($page - 1) * $size, $size));

// 响应 JSON
// ========================================

// 设置响应类型为 JSON
header('Content-Type: application/json');

// 输出 JSON
echo json_encode(array(
  'success' => true,
  'data' => $comments,
  'total_count' => $total_count
));
