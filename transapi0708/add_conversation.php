<?php
session_start();
include 'db.php';  // 连接数据库

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 检查用户是否已登录
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        $response = array('success' => false, 'error' => 'User not logged in.');
        echo json_encode($response);
        exit();
    }

    // 从会话中获取 user_id
    $user_id = $_SESSION['user_id'];

    // 从 POST 数据中获取 topic
    $topic = $_POST['topic'];

    // 插入新会话
    $stmt = $conn->prepare("INSERT INTO sessions (user_id, topic) VALUES (?, ?)");
    if ($stmt === false) {
        $response = array('success' => false, 'error' => $conn->error);
        echo json_encode($response);
        exit();
    }
    
    $stmt->bind_param("is", $user_id, $topic);

    if ($stmt->execute()) {
        $response = array('success' => true, 'session_id' => $stmt->insert_id, 'topic' => $topic);
    } else {
        $response = array('success' => false, 'error' => $stmt->error);
    }

    $stmt->close();
    echo json_encode($response);
}

$conn->close();
?>

