<?php
/**
 * 新增文章
 */

// 载入脚本
// ========================================

require '../functions.php';

// 访问控制
// ========================================

// 获取登录用户信息
xiu_get_current_user();

// 处理提交请求
// ========================================

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // 数据校验
  // ------------------------------

  if (empty($_POST['slug'])
    || empty($_POST['title'])
    || empty($_POST['created'])
    || empty($_POST['content'])
    || empty($_POST['status'])
    || empty($_POST['category'])) {
    // 缺少必要数据
    $message = '请完整填写所有内容';
  } else if (xiu_query(sprintf("select count(1) from posts where slug = '%s'", $_POST['slug']))[0][0] > 0) {
    // slug 重复
    $message = '别名已经存在，请修改别名';
  } else {
    // 接收数据
    // ------------------------------

    $slug = $_POST['slug'];
    $title = $_POST['title'];
    $feature = ''; // 图片稍后再考虑
    $created = $_POST['created'];
    $content = $_POST['content'];
    $status = $_POST['status'];
    $user_id = $current_user['id'];
    $category_id = $_POST['category'];

    // 保存数据
    // ------------------------------

    // 拼接查询语句
    $sql = sprintf(
      "insert into posts values (null, '%s', '%s', '%s', '%s', '%s', 0, 0, '%s', %d, %d)",
      $slug,
      $title,
      $feature,
      $created,
      $content,
      $status,
      $user_id,
      $category_id
    );

    // 执行 SQL 保存数据
    if (xiu_execute($sql) > 0) {
      // 保存成功 跳转
      header('Location: /admin/posts.php');
      exit;
    } else {
      // 保存失败
      $message = '保存失败，请重试';
    }
  }
}

// 查询数据
// ========================================

// 查询全部分类数据
$categories = xiu_query('select * from categories');

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Add new post &laquo; Admin</title>
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
        <li><a href="login.php"><i class="fa fa-sign-out"></i>退出</a></li>
      </ul>
    </nav>
    <div class="container-fluid">
      <div class="page-title">
        <h1>写文章</h1>
      </div>
      <?php if (isset($message)) : ?>
      <div class="alert alert-danger">
        <strong>错误！</strong><?php echo $message; ?>
      </div>
      <?php endif; ?>
      <form class="row" action="/admin/post-add.php" method="post">
        <div class="col-md-9">
          <div class="form-group">
            <label for="title">标题</label>
            <input id="title" class="form-control input-lg" name="title" type="text" placeholder="文章标题">
          </div>
          <div class="form-group">
            <label for="content">标题</label>
            <textarea id="content" class="form-control input-lg" name="content" cols="30" rows="10" placeholder="内容"></textarea>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="slug">别名</label>
            <input id="slug" class="form-control" name="slug" type="text" placeholder="slug">
            <p class="help-block">https://zce.me/post/<strong>slug</strong></p>
          </div>
          <div class="form-group">
            <label for="feature">特色图像</label>
            <!-- show when image chose -->
            <img class="help-block thumbnail" style="display: none">
            <input id="feature" class="form-control" name="feature" type="file" placeholder="feature">
          </div>
          <div class="form-group">
            <label for="category">所属分类</label>
            <select id="category" class="form-control" name="category">
              <?php foreach ($categories as $item) { ?>
              <option value="<?php echo $item['id']; ?>"><?php echo $item['name']; ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group">
            <label for="created">发布时间</label>
            <input id="created" class="form-control" name="created" type="datetime-local">
          </div>
          <div class="form-group">
            <label for="status">状态</label>
            <select id="status" class="form-control" name="status">
              <option value="draft">草稿</option>
              <option value="publish">已发布</option>
            </select>
          </div>
          <div class="form-group">
            <button class="btn btn-primary" type="submit">保存</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <?php $current_page = 'post-add'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>NProgress.done()</script>
</body>
</html>
