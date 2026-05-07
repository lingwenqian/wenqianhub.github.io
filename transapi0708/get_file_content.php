<?php
session_start();
include 'db.php';  // 引入数据库连接

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

// 获取文件名和时间
$filename = isset($_POST['filename']) ? $_POST['filename'] : null;
$filetime = isset($_POST['time']) ? $_POST['time'] : null; // 使用time作为参数名
$userId = $_SESSION['user_id'];

if ($filename === null || $filetime === null) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Filename and time are required']);
    exit();
}

// 查询文件内容
$sql = "SELECT content FROM files WHERE filename = ? AND time = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . htmlspecialchars($conn->error)]);
    exit();
}

$stmt->bind_param('ssi', $filename, $filetime, $userId);
$stmt->execute();
$result = $stmt->get_result();

// 检查查询是否成功
if ($result === FALSE || $result->num_rows == 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'File not found']);
    exit();
}

$row = $result->fetch_assoc();
$content = $row['content'];

// 检查是否获取到文件内容
if ($content === FALSE) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Failed to read file content']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'content' => $content]);
}

$stmt->close();
$conn->close();
?>
