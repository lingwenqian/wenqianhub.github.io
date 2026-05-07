<?php
session_start();
include 'db.php';  // 引入数据库连接

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

// 获取用户 ID
$userId = $_SESSION['user_id'];

// 查询文件信息
$sql = "SELECT file_id, filename, time, filetype FROM files WHERE user_id = '$userId'";
$result = $conn->query($sql);

// 检查查询是否成功
if ($result === FALSE) {
    echo json_encode(['success' => false, 'error' => 'Database query failed: ' . $conn->error]);
    exit();
}

$files = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $files[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'files' => $files]);

$conn->close();


?>
