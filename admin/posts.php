<?php
/**
 * 文章管理
 */

// 载入脚本
// ========================================

require '../functions.php';

// 访问控制
// ========================================

// 获取登录用户信息
xiu_get_current_user();

// 处理筛选逻辑
// ========================================

// 数据库查询筛选条件（默认为 1 = 1，相当于没有条件）
$where = '1 = 1';

// 记录本次请求的查询参数
$query = '';

// 状态筛选
if (isset($_GET['s']) && $_GET['s'] != 'all') {
  $where .= sprintf(" and posts.status = '%s'", $_GET['s']);
  $query .= '&s=' . $_GET['s'];
}

// 分类筛选
if (isset($_GET['c']) && $_GET['c'] != 'all') {
  $where .= sprintf(" and posts.category_id = %d", $_GET['c']);
  $query .= '&c=' . $_GET['c'];
}

// 处理分页
// ========================================

// 定义每页显示数据量（一般把这一项定义到配置文件中）
$size = 10;

// 获取分页参数 没有或传过来的不是数字的话默认为 1
$page = isset($_GET['p']) && is_numeric($_GET['p']) ? intval($_GET['p']) : 1;

if ($page <= 0) {
  // 页码小于 1 没有任何意义，则跳转到第一页
  header('Location: /admin/posts.php?p=1' . $query);
  exit;
}

// 查询总条数
$total_count = intval(xiu_query('select count(1)
from posts
inner join users on posts.user_id = users.id
inner join categories on posts.category_id = categories.id
where ' . $where)[0][0]);

// 计算总页数
$total_pages = ceil($total_count / $size);

if ($page > $total_pages) {
  // 超出范围，则跳转到最后一页
  header('Location: /admin/posts.php?p=' . $total_pages . $query);
  exit;
}

// 查询数据
// ========================================

// 查询文章数据
$posts = xiu_query(sprintf('select
  posts.id,
  posts.title,
  posts.created,
  posts.status,
  categories.name as category_name,
  users.nickname as author_name
from posts
inner join users on posts.user_id = users.id
inner join categories on posts.category_id = categories.id
where %s
order by posts.created desc
limit %d, %d', $where, ($page - 1) * $size, $size));

// 查询全部分类数据
$categories = xiu_query('select * from categories');

// 数据过滤函数
// ========================================

/**
 * 将英文状态描述转换为中文
 * @param  string $status 英文状态
 * @return string         中文状态
 */
function convert_status ($status) {
  switch ($status) {
    case 'drafted':
      return '草稿';
    case 'published':
      return '已发布';
    case 'trashed':
      return '回收站';
    default:
      return '未知';
  }
}

/**
 * 格式化日期
 * @param  string $created 时间字符串
 * @return string          格式化后的时间字符串
 */
function format_date ($created) {
  // 设置默认时区！！！ PRC 指的是中华人民共和国
  date_default_timezone_set('PRC');

  // 转换为时间戳
  $timestamp = strtotime($created);

  // 格式化并返回 由于 r 是特殊字符，所以需要 \r 转义一下
  return date('Y年m月d日 <b\r> H:i:s', $timestamp);
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Posts &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/vendors/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
  <script src="/static/assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
  <script>NProgress.start()</script>

  <div class="main">
    <nav class="navbar">
      <button class="btn btn-default navbar-btn fa fa-bars"></button>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="profile.php"><i class="fa fa-user"></i>个人中心</a></li>
        <li><a href="logout.php"><i class="fa fa-sign-out"></i>退出</a></li>
      </ul>
    </nav>
    <div class="container-fluid">
      <div class="page-title">
        <h1>所有文章</h1>
        <a href="post-add.php" class="btn btn-primary btn-xs">写文章</a>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong> 发生XXX错误
      </div> -->
      <div class="page-action">
        <!-- show when multiple checked -->
        <a class="btn btn-danger btn-sm btn-delete" href="/admin/post-delete.php" style="display: none">批量删除</a>
        <form class="form-inline" action="/admin/posts.php">
          <select name="c" class="form-control input-sm">
            <option value="all">所有分类</option>
            <?php foreach ($categories as $item) { ?>
            <option value="<?php echo $item['id']; ?>"<?php echo isset($_GET['c']) && $_GET['c'] == $item['id'] ? ' selected' : ''; ?>><?php echo $item['name']; ?></option>
            <?php } ?>
          </select>
          <select name="s" class="form-control input-sm">
            <option value="all">所有状态</option>
            <option value="drafted"<?php echo isset($_GET['s']) && $_GET['s'] == 'drafted' ? ' selected' : ''; ?>>草稿</option>
            <option value="published"<?php echo isset($_GET['s']) && $_GET['s'] == 'published' ? ' selected' : ''; ?>>已发布</option>
            <option value="trashed"<?php echo isset($_GET['s']) && $_GET['s'] == 'trashed' ? ' selected' : ''; ?>>回收站</option>
          </select>
          <button class="btn btn-default btn-sm">筛选</button>
        </form>
        <ul class="pagination pagination-sm pull-right">
          <?php xiu_pagination($page, $total_pages, '?p=%d' . $query); ?>
        </ul>
      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="40"><input type="checkbox"></th>
            <th>标题</th>
            <th>作者</th>
            <th>分类</th>
            <th class="text-center">发表时间</th>
            <th class="text-center">状态</th>
            <th class="text-center" width="100">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $item) { ?>
          <tr data-id="<?php echo $item['id']; ?>">
            <td class="text-center"><input type="checkbox"></td>
            <td><?php echo $item['title']; ?></td>
            <td><?php echo $item['author_name']; ?></td>
            <td><?php echo $item['category_name']; ?></td>
            <td class="text-center"><?php echo format_date($item['created']); ?></td>
            <td class="text-center"><?php echo convert_status($item['status']); ?></td>
            <td class="text-center">
              <a href="javascript:;" class="btn btn-default btn-xs">编辑</a>
              <a href="/admin/post-delete.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-xs">删除</a>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php $current_page = 'posts'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>
    $(function () {
      // 获取所需操作的界面元素
      var $btnDelete = $('.btn-delete')
      var $thCheckbox = $('th > input[type=checkbox]')
      var $tdCheckbox = $('td > input[type=checkbox]')

      // 用于记录界面上选中行的数据 ID
      var checked = []

      /**
       * 表格中的复选框选中发生改变时控制删除按钮的链接参数和显示状态
       */
      $tdCheckbox.on('change', function () {
        var $this = $(this)

        // 为了可以在这里获取到当前行对应的数据 ID
        // 在服务端渲染 HTML 时，给每一个 tr 添加 data-id 属性，记录数据 ID
        // 这里通过 data-id 属性获取到对应的数据 ID
        var id = parseInt($this.parent().parent().data('id'))

        // ID 如果不合理就忽略
        if (!id) return

        if ($this.prop('checked')) {
          // 选中就追加到数组中
          checked.push(id)
        } else {
          // 未选中就从数组中移除
          checked.splice(checked.indexOf(id), 1)
        }

        // 有选中就显示操作按钮，没选中就隐藏
        checked.length ? $btnDelete.fadeIn() : $btnDelete.fadeOut()

        // 批量删除按钮链接参数
        // search 是 DOM 标准属性，用于设置或获取到的是 a 链接的查询字符串
        $btnDelete.prop('search', '?id=' + checked.join(','))
      })

      /**
       * 全选 / 全不选
       */
      $thCheckbox.on('change', function () {
        var checked = $(this).prop('checked')
        // 设置每一行的选中状态并触发 上面 👆 的事件
        $tdCheckbox.prop('checked', checked).trigger('change')
      })
    })
  </script>
  <script>NProgress.done()</script>
</body>
</html>
