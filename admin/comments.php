<?php
/**
 * 评论管理
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
  <title>Comments &laquo; Admin</title>
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
        <h1>所有评论</h1>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong> 发生XXX错误
      </div> -->
      <div class="page-action">
        <!-- show when multiple checked -->
        <div class="btn-batch" style="display: none">
          <button class="btn btn-info btn-sm">批量批准</button>
          <button class="btn btn-warning btn-sm">批量拒绝</button>
          <button class="btn btn-danger btn-sm">批量删除</button>
        </div>
        <ul class="pagination pagination-sm pull-right"></ul>
      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="40"><input type="checkbox"></th>
            <th>作者</th>
            <th width="500">评论</th>
            <th>评论在</th>
            <th>提交于</th>
            <th>状态</th>
            <th class="text-center" width="140">操作</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <?php $current_page = 'comments'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script src="/static/assets/vendors/jsrender/jsrender.js"></script>
  <script src="/static/assets/vendors/twbs-pagination/jquery.twbsPagination.js"></script>
  <script id="comment_tmpl" type="text/x-jsrender">
    {{if success}}
    {{for data}}
    <tr class="{{: status === 'held' ? 'warning' : status === 'rejected' ? 'danger' : '' }}" data-id="{{: id }}">
      <td class="text-center"><input type="checkbox"></td>
      <td>{{: author }}</td>
      <td>{{: content }}</td>
      <td>《{{: post_title }}》</td>
      <td>{{: created}}</td>
      <td>{{: status === 'held' ? '待审' : status === 'rejected' ? '拒绝' : '准许' }}</td>
      <td class="text-center">
        {{if status === 'held'}}
        <a class="btn btn-info btn-xs btn-edit" href="javascript:;" data-status="approved">批准</a>
        <a class="btn btn-warning btn-xs btn-edit" href="javascript:;" data-status="rejected">拒绝</a>
        {{/if}}
        <a class="btn btn-danger btn-xs btn-delete" href="javascript:;">删除</a>
      </td>
    </tr>
    {{/for}}
    {{else}}
    <tr>
      <td colspan="7">{{: message }}</td>
    </tr>
    {{/if}}
  </script>
  <script>
    $(function () {
      var $alert = $('.alert')
      var $tbody = $('tbody')
      var $tmpl = $('#comment_tmpl')
      var $pagination = $('.pagination')
      var $btnBatch = $('.btn-batch')

      // 页大小
      var size = 30
      // 当前页码
      var currentPage = parseInt(window.localStorage.getItem('last_comments_page')) || 1
      // 选中项集合
      var checkedItems = []

      /**
       * 加载指定页数据
       */
      function loadData () {
        $.get('/admin/comment-list.php', { p: currentPage, s: size }, function (res) {
          // 通过模板引擎渲染数据
          var html = $tmpl.render(res)
          // 设置到页面中
          $tbody.html(html)
        })
      }

      // 页面加载完成过后，发送异步请求获取评论数据
      $.get('/admin/comment-list.php', { p: currentPage, s: size }, function (res) {
        // 通过模板引擎渲染数据
        var html = $tmpl.render(res)
        // 设置到页面中
        $tbody.html(html)
        // 分页组件
        $pagination.twbsPagination({
          startPage: currentPage,
          totalPages: Math.ceil(res.total_count / size),
          initiateStartPageClick: false, // 否则 onPageClick 第一次就会触发
          onPageClick: function (e, page) {
            currentPage = page
            window.localStorage.setItem('last_comments_page', currentPage)
            loadData()
          }
        })
      })

      // 删除评论
      $tbody.on('click', '.btn-delete', function () {
        var $tr = $(this).parent().parent()
        var id = parseInt($tr.data('id'))
        $.get('/admin/comment-delete.php', { id: id }, function (res) {
          res.success && loadData()
        })
      })

      // 修改评论状态
      $tbody.on('click', '.btn-edit', function () {
        var id = parseInt($(this).parent().parent().data('id'))
        var status = $(this).data('status')
        $.post('/admin/comment-status.php?id=' + id, { status: status }, function (res) {
          res.success && loadData()
        })
      })

      // 批量操作按钮
      $tbody.on('change', 'td > input[type=checkbox]', function () {
        var id = parseInt($(this).parent().parent().data('id'))
        if ($(this).prop('checked')) {
          checkedItems.push(id)
        } else {
          checkedItems.splice(checkedItems.indexOf(id), 1)
        }
        checkedItems.length ? $btnBatch.fadeIn() : $btnBatch.fadeOut()
      })

      // 全选 / 全不选
      $('th > input[type=checkbox]').on('change', function () {
        var checked = $(this).prop('checked')
        $('td > input[type=checkbox]').prop('checked', checked).trigger('change')
      })

      // 批量操作
      $btnBatch
        // 批准
        .on('click', '.btn-info', function () {
          $.post('/admin/comment-status.php?id=' + checkedItems.join(','), { status: 'approved' }, function (res) {
            res.success && loadData()
          })
        })
        // 拒绝
        .on('click', '.btn-warning', function () {
          $.post('/admin/comment-status.php?id=' + checkedItems.join(','), { status: 'rejected' }, function (res) {
            res.success && loadData()
          })
        })
        // 删除
        .on('click', '.btn-danger', function () {
          $.get('/admin/comment-delete.php', { id: checkedItems.join(',') }, function (res) {
            res.success && loadData()
          })
        })
    })
  </script>
  <script>NProgress.done()</script>
</body>
</html>
