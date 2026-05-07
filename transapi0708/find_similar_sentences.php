<?php
session_start();
include 'db.php';  // 连接数据库

header('Content-Type: application/json');  // 确保返回 JSON 数据

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 获取原始的POST数据
        $rawPostData = file_get_contents('php://input');
        // 将JSON字符串解析成PHP数组
        $postData = json_decode($rawPostData, true);

        $text = isset($postData['text']) ? $postData['text'] : '';
        $session_id = isset($postData['session_id']) ? intval($postData['session_id']) : 1;
        $selected_files = isset($postData['files']) ? $postData['files'] : [];
        $knowledge_ids = [];
        $most_similar_sentences = [];
        //echo json_encode(['selected_files' => $selected_files]);

        // 调用FastAPI获取问题的向量表示
        $url = 'http://127.0.0.1:8001/get-sentence-embedding/';
        $data = json_encode(['text' => $text]);
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => $data,
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === false) {
            echo json_encode(['error' => 'Error retrieving embedding']);
            exit();
        }
        $response = json_decode($result, true);
        if (!isset($response['embedding'])) {
            echo json_encode(['error' => 'Invalid embedding response']);
            exit();
        }
        $input_embedding = $response['embedding'];
        $qtime = date('Y-m-d H:i:s');

        // 从文件名列表获取文件ID列表
        $file_ids = [];
        if (!empty($selected_files)) {//检查该列表是否为空
            //将文件名转换为SQL查询字符串
            $file_names_str = implode(',', array_map(function($filename) {
                return "'" . addslashes($filename) . "'";
            }, $selected_files));

            $sql = "SELECT file_id FROM files WHERE filename IN ($file_names_str)";
            $result = $conn->query($sql);

            if ($result) {//如果查询成功
                while ($row = $result->fetch_assoc()) {//使用while循环遍历查询结果的每一行
                    $file_ids[] = intval($row['file_id']);//将获取的file_id值转换为整数，并添加到$file_ids数组中
                }
            } else {//如果查询失败
                echo json_encode(['error' => 'Error querying database for file IDs']);
                exit();
            }
        }

        // 查询knowledge表，计算输入句子与每个句子的余弦相似度
        if (!empty($file_ids)) {//检查是否为空
            $file_ids_str = implode(',', $file_ids);
            //查询知识表中与这些文件ID相关联的条目
            $sql = "SELECT k.knowledge_id, k.text, k.embedding FROM knowledge k JOIN files f ON k.file_id = f.file_id WHERE k.file_id IN ($file_ids_str)";
        } else {//查询所有知识条目，而不考虑特定的文件
            $sql = "SELECT k.knowledge_id, k.text, k.embedding FROM knowledge k";
        }
        $result = $conn->query($sql);//执行构建好的 SQL 查询语句，将结果保存在 $result 中

        if (!$result) {
            echo json_encode(['error' => 'Error querying database']);
            exit();
        }

        $similar_sentences = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {//处理每一行数据
                $knowledge_vector = json_decode($row['embedding']);
                $dot_product = 0;
                $input_norm = 0;
                $knowledge_norm = 0;
                for ($i = 0; $i < count($input_embedding); $i++) {
                    $dot_product += $input_embedding[$i] * $knowledge_vector[$i];
                    $input_norm += $input_embedding[$i] * $input_embedding[$i];
                    $knowledge_norm += $knowledge_vector[$i] * $knowledge_vector[$i];
                }
                $input_norm = sqrt($input_norm);
                $knowledge_norm = sqrt($knowledge_norm);
                $similarity = ($input_norm == 0 || $knowledge_norm == 0) ? 0 : $dot_product / ($input_norm * $knowledge_norm);
                if ($similarity > 0.3) {
                    $similar_sentences[] = [
                        'knowledge_id' => $row['knowledge_id'],
                        'text' => $row['text'],
                        'similarity' => $similarity,
                    ];
                }
            }
        }

        usort($similar_sentences, function ($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        if (count($similar_sentences) >= 4) {
            $most_similar_sentences = array_slice($similar_sentences, 0, 3);
        } elseif (count($similar_sentences) > 0) {
            $most_similar_sentences = $similar_sentences;
        }

        usort($most_similar_sentences, function ($a, $b) {
            return $a['knowledge_id'] <=> $b['knowledge_id'];
        });

        $knowledge_ids = array_column($most_similar_sentences, 'knowledge_id');
        $context_sentences = array_column($most_similar_sentences, 'text');
        //$file_sources = array_column($most_similar_sentences, 'filename');//从 $most_similar_sentences 数组中提取 filename 列的所有值，并将它们存储在一个新数组 $file_sources 中
        $context_str = implode("\n\n", $context_sentences);
        $context_sentences = [];

        // 构建发送给FastAPI的数据
        $data = json_encode([
            'text' => $text,
            'context' => $context_str
        ]);
        error_log("Sending data: " . $data);  // 添加调试信息

        $url = 'http://127.0.0.1:8001/answer/';
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => $data,
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === false) {
            echo json_encode(['error' => 'Error retrieving answer']);
            exit();
        }
        error_log("Received response: " . $result);  // 添加调试信息

        $response = json_decode($result, true);
        if (!isset($response['answer'])) {
            echo json_encode(['error' => 'Invalid answer response']);
            exit();
        }
        $answer = $response['answer'];
        $atime = date('Y-m-d H:i:s');

        $knowledge_id_str = count($knowledge_ids) > 0 ? implode(",", $knowledge_ids) : null;
        $sql = "INSERT INTO dialogues (session_id, knowledge_id, q, a, qtime, atime) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $session_id, $knowledge_id_str, $text, $answer, $qtime, $atime);
        $stmt->execute();

        $conn->close();

        echo json_encode(['answer' => $answer, 'knowledge' => $context_str]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
    }
}
?>
