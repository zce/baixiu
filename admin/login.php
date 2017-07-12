<?php
/**
 * 登录页面
 */

// 载入配置文件
require_once '../config.php';

// 判断当前是否是 POST 请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 如果是 POST 提交则处理登录业务逻辑
  if (empty($_POST['email']) || empty($_POST['password'])) {
    // 没有完整填写表单，定义一个变量存放错误消息，在渲染 HTML 时显示到页面上
    $message = '请完整填写表单';
  } else {
    // 接收表单参数
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 邮箱与密码是否匹配（数据库查询）
    // 建立与数据的连接
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if (!$connection) {
      // 链接数据库失败，打印错误信息，注意：生产环境不能输出具体的错误信息（不安全）
      die('<h1>Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error() . '</h1>');
    }

    // 根据邮箱查询用户信息，limit 是为了提高查询效率
    $result = mysqli_query($connection, sprintf("select * from users where email = '%s' limit 1", $email));

    if ($result) {
      // 查询成功，获取查询结果
      if ($user = mysqli_fetch_assoc($result)) {
        // 用户存在，密码比对
        if ($user['password'] == $password) {
          // 启用新会话或使用已有会话（打开用户的箱子，如果该用户没有箱子，给他一个新的空箱子）
          session_start();
          // 记住登录状态
          // $_SESSION['is_logged_in'] = true;
          $_SESSION['current_logged_in_user_id'] = $user['id'];
          // 匹配则跳转到 /admin/index.php
          header('Location: /admin/index.php');
          exit; // 结束脚本的执行
        }
      }
      $message = '邮箱与密码不匹配';
      // 释放资源
      mysqli_free_result($result);
    } else {
      // 查询失败
      $message = '邮箱与密码不匹配';
    }
    // 关闭与数据库之间的连接
    mysqli_close($connection);
  }
}
// 以下就是直接输出 HTML 内容
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Sign in &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
</head>
<body>
  <div class="login">
    <form class="login-wrap" action="/admin/login.php" method="post">
      <img class="avatar" src="/static/assets/img/default.png">
      <?php if (isset($message)) : ?>
      <div class="alert alert-danger">
        <strong>错误！</strong><?php echo $message; ?>
      </div>
      <?php endif; ?>
      <div class="form-group">
        <label for="email" class="sr-only">邮箱</label>
        <input id="email" name="email" type="email" class="form-control" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" placeholder="邮箱" autofocus>
      </div>
      <div class="form-group">
        <label for="password" class="sr-only">密码</label>
        <input id="password" name="password" type="password" class="form-control" placeholder="密码">
      </div>
      <button class="btn btn-primary btn-block" type="submit">登 录</button>
    </form>
  </div>
</body>
</html>
