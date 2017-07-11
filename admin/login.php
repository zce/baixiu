<?php
/**
 * 登录页面
 */

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

    // 邮箱与密码是否匹配（假数据比对）
    if ($email === 'admin@demo.com' && $password === 'wanglei') {
      // 匹配则跳转到 /admin/index.php
      header('Location: /admin/index.php');
      exit; // 结束脚本的执行
    } else {
      // 不匹配则提示错误信息
      $message = '邮箱与密码不匹配';
    }
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
        <input id="email" name="email" type="email" class="form-control" placeholder="邮箱" autofocus>
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
