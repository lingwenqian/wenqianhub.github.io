<?php
session_start();
header('Content-Type: application/json');
include 'db.php';  // 引入数据库连接

// 检查用户是否已登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'User not logged in.']);
    exit();
}

// 获取文件名和时间
$filename = isset($_POST['filename']) ? $_POST['filename'] : null;
$filetime = isset($_POST['time']) ? $_POST['time'] : null;
$userId = $_SESSION['user_id'];

if ($filename === null || $filetime === null) {
    echo json_encode(['success' => false, 'error' => 'Filename and time are required']);
    exit();
}

// 开始事务
$conn->begin_transaction();

// 获取 file_id
$sql = "SELECT file_id FROM files WHERE filename = ? AND time = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Prepare failed for file_id: ' . htmlspecialchars($conn->error)]);
    exit();
}
$stmt->bind_param('ssi', $filename, $filetime, $userId);
$stmt->execute();
$result = $stmt->get_result();

// 检查查询是否成功
if ($result === FALSE || $result->num_rows == 0) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'File not found']);
    exit();
}

$row = $result->fetch_assoc();
$file_id = $row['file_id'];
$stmt->close();

// 删除 knowledge 表中的关联数据
$sql = "DELETE FROM knowledge WHERE file_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Prepare failed for knowledge delete: ' . htmlspecialchars($conn->error)]);
    exit();
}
$stmt->bind_param('i', $file_id);
if (!$stmt->execute()) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Failed to delete knowledge record from database']);
    exit();
}
$stmt->close();

// 从数据库中删除文件记录
$sql = "DELETE FROM files WHERE file_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Prepare failed for delete: ' . htmlspecialchars($conn->error)]);
    exit();
}
$stmt->bind_param('i', $file_id);

if ($stmt->execute()) {
    $conn->commit();
    echo json_encode(['success' => true]);
} else {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Failed to delete file record from database']);
}

$stmt->close();
$conn->close();
?>
