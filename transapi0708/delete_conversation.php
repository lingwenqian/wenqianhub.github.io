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

    // 从 POST 数据中获取 session_id
    $data = json_decode(file_get_contents('php://input'), true);
    $session_id = $data['session_id'];

    // 从会话中获取 user_id
    $user_id = $_SESSION['user_id'];

    // 开始事务
    $conn->begin_transaction();

    // 删除相关的对话记录
    $stmt = $conn->prepare("DELETE FROM dialogues WHERE session_id = ?");
    if ($stmt === false) {
        $conn->rollback();
        $response = array('success' => false, 'error' => 'Prepare failed for dialogues: ' . htmlspecialchars($conn->error));
        echo json_encode($response);
        exit();
    }
    $stmt->bind_param("i", $session_id);
    if (!$stmt->execute()) {
        $conn->rollback();
        $response = array('success' => false, 'error' => 'Execute failed for dialogues: ' . htmlspecialchars($stmt->error));
        echo json_encode($response);
        exit();
    }
    $stmt->close();

    // 删除会话记录
    $stmt = $conn->prepare("DELETE FROM sessions WHERE session_id = ? AND user_id = ?");
    if ($stmt === false) {
        $conn->rollback();
        $response = array('success' => false, 'error' => 'Prepare failed for sessions: ' . htmlspecialchars($conn->error) . '. SQL: DELETE FROM sessions WHERE session_id = ' . $session_id . ' AND user_id = ' . $user_id);
        echo json_encode($response);
        exit();
    }
    $stmt->bind_param("ii", $session_id, $user_id);
    if (!$stmt->execute()) {
        $conn->rollback();
        $response = array('success' => false, 'error' => 'Execute failed for sessions: ' . htmlspecialchars($stmt->error));
        echo json_encode($response);
        exit();
    } else {
        $conn->commit();
        $response = array('success' => true);
    }

    $stmt->close();
    echo json_encode($response);
}

$conn->close();
?>
