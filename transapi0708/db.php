<?php
// 连接到 MySQL 数据库
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "transapi";

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
?>