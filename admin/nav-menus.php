<?php
/**
 * 导航菜单管理
 */

// 载入脚本
// ========================================

require '../functions.php';

// 访问控制
// ========================================

// 获取登录用户信息
xiu_get_current_user();

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Navigation menus &laquo; Admin</title>
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
        <h1>导航菜单</h1>
      </div>
      <div class="alert alert-danger" style="display: none;"></div>
      <div class="row">
        <div class="col-md-4">
          <form>
            <h2>添加新导航链接</h2>
            <div class="form-group">
              <label for="icon">图标 Class</label>
              <input id="icon" class="form-control" name="icon" type="text" placeholder="图标 Class">
            </div>
            <div class="form-group">
              <label for="text">文本</label>
              <input id="text" class="form-control" name="text" type="text" placeholder="文本">
            </div>
            <div class="form-group">
              <label for="title">标题</label>
              <input id="title" class="form-control" name="title" type="text" placeholder="标题">
            </div>
            <div class="form-group">
              <label for="link">链接</label>
              <input id="link" class="form-control" name="link" type="text" placeholder="链接">
            </div>
            <div class="form-group">
              <button class="btn btn-primary btn-save" type="submit">添加</button>
            </div>
          </form>
        </div>
        <div class="col-md-8">
          <div class="page-action">
            <!-- show when multiple checked -->
            <a class="btn btn-danger btn-sm" href="javascript:;" style="display: none">批量删除</a>
          </div>
          <table class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th class="text-center" width="40"><input type="checkbox"></th>
                <th>文本</th>
                <th>标题</th>
                <th>链接</th>
                <th class="text-center" width="100">操作</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <?php $current_page = 'nav-menus'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script src="/static/assets/vendors/jsrender/jsrender.js"></script>
  <script id="menu_tmpl" type="text/x-jsrender">
    <tr>
      <td class="text-center"><input type="checkbox"></td>
      <td><i class="{{: icon }}"></i>{{: text }}</td>
      <td>{{: title }}</td>
      <td>{{: link }}</td>
      <td class="text-center">
        <button class="btn btn-danger btn-xs btn-delete" data-index="{{: #index }}">删除</button>
      </td>
    </tr>
  </script>
  <script>
    $(function () {
      /**
       * 显示消息
       * @param  {String} msg 消息文本
       */
      function notify (msg) {
        $('.alert').text(msg).fadeIn()
        // 3000 ms 后隐藏
        setTimeout(function () {
          $('.alert').fadeOut()
        }, 3000)
      }

      /**
       * 加载导航菜单数据
       */
      function loadData () {
        $.get('/admin/options.php', { key: 'nav_menus' }, function (res) {
          if (!res.success) {
            // 失败，提示
            return notify(res.message)
          }

          var menus = []

          try {
            // 尝试以 JSON 方式解析响应内容
            menus = JSON.parse(res.data)
          } catch (e) {
            notify('获取数据失败')
          }

          // 渲染
          $('tbody').html($('#menu_tmpl').render(menus))
        })
      }

      // 首次加载数据
      loadData()
    })
  </script>
  <script>NProgress.done()</script>
</body>
</html>
