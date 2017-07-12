<?php
/**
 * 封装常用函数
 */

// 载入配置选项，为了防止 functions.php 重复被载入时载入配置报错，所以使用 require_once
require_once '../config.php';

/**
 * 根据配置文件信息创建一个数据库连接，注意用完以后需要关闭
 * @return mysqli 数据库连接对象
 */
function xiu_connect () {
  $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

  if (!$connection) {
    // 如果连接失败报错
    die('<h1>Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error() . '</h1>');
  }

  return $connection;
}

/**
 * 执行一个查询语句，返回查询到的数据（关联数组混合索引数组）
 * @param  string $sql 需要执行的查询语句
 * @return array       查询到的数据（二维数组）
 */
function xiu_query ($sql) {
  // 获取数据库连接
  $connection = xiu_connect();

  // 定义结果数据容器，用于装载查询到的数据
  $data = array();

  // 执行参数中指定的 SQL 语句
  if ($result = mysqli_query($connection, $sql)) {
    // 查询成功，则获取结果集中的数据

    // 遍历每一行的数据
    while ($row = mysqli_fetch_array($result)) {
      // 追加到结果数据容器中
      $data[] = $row;
    }

    // 释放结果集
    mysqli_free_result($result);
  }

  // 关闭数据库连接
  mysqli_close($connection);

  // 返回容器中的数据
  return $data;
}

/**
 * 获取当前登录用户的信息
 * 如果没有获取到的话则跳转到登录页
 * 也可以通过全局变量访问返回结果
 * @return array 包含用户信息的关联数组
 */
function xiu_get_current_user () {
  if (isset($GLOBALS['current_user'])) {
    // 已经执行过了（重复调用导致）
    return $GLOBALS['current_user'];
  }

  // 启动会话
  session_start();

  if (empty($_SESSION['current_logged_in_user_id']) || !is_numeric($_SESSION['current_logged_in_user_id'])) {
    // 没有登录标识就代表没有登录
    // 跳转到登录页
    header('Location: /admin/login.php');
    exit; // 结束代码继续执行
  }

  // 根据 ID 获取当前登录用户信息（定义成全局的，方便后续使用）
  $GLOBALS['current_user'] = xiu_query(sprintf('select * from users where id = %d limit 1', intval($_SESSION['current_logged_in_user_id'])))[0];

  return $GLOBALS['current_user'];
}
