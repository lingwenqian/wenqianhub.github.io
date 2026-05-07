<?php
//这段PHP代码用于获取某个用户的所有会话及其对应的对话记录，包括用户的问题、AI的回答和相关的知识文本
include 'db.php';  // 连接数据库

// 启动会话以获取用户ID（假设用户ID为1，实际应用中应获取当前登录用户的ID）
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: process.php");
    exit();
}

// 获取用户 ID
$userId = $_SESSION['user_id'];

// 获取所有会话
$sessionsQuery = $conn->prepare("SELECT * FROM sessions WHERE user_id = ?");
$sessionsQuery->bind_param("i", $userId);
$sessionsQuery->execute();
$sessionsResult = $sessionsQuery->get_result();

// 初始化会话数组
$sessions = [];
while ($session = $sessionsResult->fetch_assoc()) {
    $sessionId = $session['session_id'];
    $sessions[$sessionId] = [
        'topic' => $session['topic'],
        'messages' => []
    ];

    // 获取该会话的所有消息，包括用户问题、AI回答和知识文本
    $messagesQuery = $conn->prepare("SELECT d.q, d.a, d.knowledge_id FROM dialogues d WHERE d.session_id = ? ORDER BY d.qtime ASC");

    $messagesQuery->bind_param("i", $sessionId);
    $messagesQuery->execute();
    $messagesResult = $messagesQuery->get_result();

    // 处理每条消息
    while ($message = $messagesResult->fetch_assoc()) {
        if ($message['q']) {
            $sessions[$sessionId]['messages'][] = [
                'type' => 'user',
                'text' => $message['q']
            ];
        }
        if ($message['a']) {
            $sessions[$sessionId]['messages'][] = [
                'type' => 'api',
                'text' => $message['a']
            ];
        }
        if ($message['knowledge_id']) {
            // 将knowledge_id拆分为数组
            $knowledgeIds = explode(",", $message['knowledge_id']);
            $knowledgeTexts = [];
            // 获取每个knowledge_id对应的知识文本
            foreach ($knowledgeIds as $knowledgeId) {
                $knowledgeQuery = $conn->prepare("SELECT text FROM knowledge WHERE knowledge_id = ?");
                $knowledgeQuery->bind_param("i", $knowledgeId);
                $knowledgeQuery->execute();
                $knowledgeResult = $knowledgeQuery->get_result();

                if ($knowledge = $knowledgeResult->fetch_assoc()) {
                    $knowledgeTexts[] = $knowledge['text'];
                }
                // if ($knowledge = $knowledgeResult->fetch_assoc()) {
                //     $text = $knowledge['text'];

                //     // 如果当前知识片段不是第一个，并且上一个片段的ID与当前片段的ID是连续的，去除重叠部分
                //     if ($index > 0 && ($knowledgeIds[$index] - $knowledgeIds[$index - 1] == 1)) {
                //         $text = mb_substr($text, 100);  // 去除前100个汉字
                //     }

                //     $knowledgeTexts[] = $text;
                // }
            }

            // 将所有知识句子连接成一个字符串
            if (!empty($knowledgeTexts)) {
                $knowledgeStr = implode("\n\n", $knowledgeTexts);
                $sessions[$sessionId]['messages'][] = [
                    'type' => 'k',
                    'text' => $knowledgeStr
                ];
            }
        }
    }
}

?>