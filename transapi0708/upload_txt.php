<?php

// 去除缩进函数
function cleanContent($text) {
    // 去除所有类型的换行符
    $text = preg_replace('/\r\n|\r|\n/', '', $text);
    // 去除全角空格（使用实际的全角空格字符）
    $text = str_replace('　', '', $text);
    return $text;
}

/**
 * 判断字符串是否包含中文字符
 *
 * @param string $string 需要检查的字符串
 * @return bool 如果包含中文字符返回 true，否则返回 false
 */
function isChinese($string) {
    // 使用正则表达式检查字符串中是否包含中文字符
    return preg_match('/[\x{4e00}-\x{9fa5}]/u', $string);
}

/**
 * 切片主函数，根据文本内容自动选择适当的切片方法
 *
 * @param string $text 需要切片的文本
 * @return array 切片后的文本段落数组
 */
function sliceContent($text) {
    $hasChinese = isChinese($text);
    $hasEnglish = preg_match('/[a-zA-Z]/', $text);

    // 根据文本内容选择切片方法
    if ($hasChinese && !$hasEnglish) {
        return sliceChineseText($text);
    } elseif (!$hasChinese && $hasEnglish) {
        return sliceEnglishText($text);
    } else {
        return sliceMixedText($text);
    }
}

/**
 * 切片中文文本
 *
 * @param string $text 需要切片的中文文本
 * @return array 切片后的文本段落数组
 */
function sliceChineseText($text) {
    // 定义最小和最大切片长度（以字符为单位）
    $minLength = 800;
    $maxLength = 1000;
    $slices = [];
    $textLength = mb_strlen($text, 'UTF-8');
    $start = 0;

    // 循环处理文本，生成切片
    while ($start < $textLength) {
        $end = $start + $maxLength;
        if ($end >= $textLength) {
            $end = $textLength;
        } else {
            // 尝试在句子结束标点符号后切分
            $subText = mb_substr($text, $start, $maxLength);
            $punctuations = '。！？!?';
            $endPos = mb_strrpos($subText, mb_substr($punctuations, 0, 1));
            for ($i = 1; $i < mb_strlen($punctuations); $i++) {
                $pos = mb_strrpos($subText, mb_substr($punctuations, $i, 1));
                if ($pos !== false && ($endPos === false || $pos > $endPos)) {
                    $endPos = $pos;
                }
            }
            if ($endPos !== false) {
                $end = $start + $endPos + 1;
            }
        }

        $slice = mb_substr($text, $start, $end - $start, 'UTF-8');
        $slices[] = $slice;
        $start = $end;
    }

    return $slices;
}

/**
 * 切片英文文本
 *
 * @param string $text 需要切片的英文文本
 * @return array 切片后的文本段落数组
 */
function sliceEnglishText($text) {
    // 定义最小和最大切片长度（以单词为单位）
    $minLength = 400;
    $maxLength = 500;
    $slices = [];
    $words = preg_split('/\s+/', $text);
    $wordCount = count($words);
    $start = 0;

    // 循环处理文本，生成切片
    while ($start < $wordCount) {
        $end = $start + $maxLength;
        if ($end >= $wordCount) {
            $end = $wordCount;
        } else {
            // 尝试在句号后切分
            $sliceWords = array_slice($words, $start, $maxLength);
            $slice = implode(' ', $sliceWords);
            $punctuations = '.!?';
            $endPos = strrpos($slice, substr($punctuations, 0, 1));
            for ($i = 1; $i < strlen($punctuations); $i++) {
                $pos = strrpos($slice, substr($punctuations, $i, 1));
                if ($pos !== false && ($endPos === false || $pos > $endPos)) {
                    $endPos = $pos;
                }
            }
            if ($endPos !== false) {
                $end = $start + count(preg_split('/\s+/', substr($slice, 0, $endPos + 1)));
            }
        }

        $slice = implode(' ', array_slice($words, $start, $end - $start));
        $slices[] = $slice;
        $start = $end;
    }

    return $slices;
}

/**
 * 切片中英混杂文本
 *
 * @param string $text 需要切片的中英混杂文本
 * @return array 切片后的文本段落数组
 */
function sliceMixedText($text) {
    $slices = [];
    $minLengthChinese = 800;
    $maxLengthChinese = 1000;

    $start = 0;
    $textLength = mb_strlen($text, 'UTF-8');

    // 循环处理文本，生成切片
    while ($start < $textLength) {
        $end = $start + $maxLengthChinese;
        if ($end >= $textLength) {
            $end = $textLength;
        } else {
            // 尝试在中文标点符号后切分
            $subText = mb_substr($text, $start, $maxLengthChinese);
            $punctuations = '。！？!?';
            $endPos = mb_strrpos($subText, mb_substr($punctuations, 0, 1));
            for ($i = 1; $i < mb_strlen($punctuations); $i++) {
                $pos = mb_strrpos($subText, mb_substr($punctuations, $i, 1));
                if ($pos !== false && ($endPos === false || $pos > $endPos)) {
                    $endPos = $pos;
                }
            }
            if ($endPos !== false) {
                $end = $start + $endPos + 1;
            }
        }

        $slice = mb_substr($text, $start, $end - $start, 'UTF-8');
        $start = $end;

        // 根据内容调用相应的切片函数
        if (isChinese($slice)) {
            $slices = array_merge($slices, sliceChineseText($slice));
        } else {
            $slices = array_merge($slices, sliceEnglishText($slice));
        }
    }

    return $slices;
}

include 'db.php';  // 引入数据库连接

session_start();

// 获取用户 ID
$userId = $_SESSION['user_id'];

# 检查请求的 HTTP 方法是否为 POST，如果是，则进入后续的处理逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    # 从 POST 请求的数据中获取表单信息，包括上传的 txt 文件名
    $txtFile = $_FILES['txt_file']['tmp_name'];

    // 检查文件类型是否为 TXT
    $fileType = pathinfo($_FILES['txt_file']['name'], PATHINFO_EXTENSION);
    if ($fileType !== 'txt') {
        // 发送错误响应
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid file type. Only TXT files are allowed.']);
        exit();
    }

    // 读取 TXT 文件内容
    $txtContent = file_get_contents($txtFile);

    // 清理内容
    $cleanedContent = cleanContent($txtContent);

    // 切片处理
    $slices = sliceContent($cleanedContent);

    // 插入文件信息到 files 表
    $filename = $_FILES['txt_file']['name'];
    $filetype = $_FILES['txt_file']['type'];
    $time = date('Y-m-d H:i:s');
    $filepath = $_FILES['txt_file']['tmp_name'];  // 保留临时路径
    $txtContent = $conn->real_escape_string($txtContent);

    $sqlFiles = "INSERT INTO files (filename, filepath, filetype, time, user_id, content)
                VALUES ('$filename', '$filepath', '$filetype', '$time', '$userId', '$txtContent')";

    if ($conn->query($sqlFiles) === FALSE) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error inserting file data into files table: ' . $sqlFiles . "<br>" . $conn->error]);
        exit();
    } else {
        // 获取刚刚插入的文件的 ID
        $fileId = $conn->insert_id;

        // 初始化结果数组，存储向量
        $textEmbeddings = array();

        // 逐行发送文本内容到 FastAPI
        $url = 'http://127.0.0.1:8001/process-text/';

        // 遍历每行文本内容，发送请求获取向量
        foreach ($slices as $index => $text) {
            // 构造请求数据
            $data = json_encode([
                'text' => $text,
            ], JSON_UNESCAPED_UNICODE); // 保持数据中非 ASCII 字符

            // 配置发送请求时的参数
            $options = [
                'http' => [
                    'header' => "Content-Type: application/json\r\n",
                    'method' => 'POST',
                    'content' => $data,
                ],
            ];
            // 创建一个流上下文对象
            $context = stream_context_create($options);
            // 调用 fastapi 网址获取向量
            $result = @file_get_contents($url, false, $context);
            // 如果无法获取结果
            if ($result === FALSE) {
                // 获取错误信息
                $error = error_get_last();
                header('Content-Type: application/json');
                echo json_encode(['error' => "Error occurred while processing line " . ($index + 1) . ": " . $error['message']]);
                exit();
            } 
            // 如果获取结果成功
            else {
                // 解析 FastAPI 返回的结果为 PHP 数组
                $response = json_decode($result, true);
                // 如果响应结果中缺少向量，输出错误消息
                if (!isset($response['embedding'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => "Invalid response from FastAPI for line " . ($index + 1)]);
                    exit();
                } 
                // 响应结果如预期，存储文本的向量
                else {
                    $textEmbeddings[] = $response['embedding'];
                }
            }
        }

        // 插入文本信息和向量到 knowledge 表
        foreach ($slices as $index => $text) {
            $escapedText = $conn->real_escape_string($text);
            // 如果向量为 null，则存储为 NULL
            $embedding = isset($textEmbeddings[$index]) ? "'" . json_encode($textEmbeddings[$index], JSON_UNESCAPED_UNICODE) . "'" : 'NULL';
            // 插入数据到 knowledge 表
            $sqlKnowledge = "INSERT INTO knowledge (file_id, text, embedding)
                            VALUES ('$fileId', '$escapedText', $embedding)";
            if ($conn->query($sqlKnowledge) === FALSE) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Error inserting data into knowledge table: ' . $sqlKnowledge . "<br>" . $conn->error]);
                exit();
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => 'TXT file processed and stored successfully.']);
}
$conn->close();
?>




