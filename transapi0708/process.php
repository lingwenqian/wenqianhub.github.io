<?php
session_start();// 启动会话，以便使用 $_SESSION 变量存储用户信息
include 'db.php';


// 检查请求方法是否为 POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 处理注册表单
    if (isset($_POST['register']) && !empty($_POST['register_name']) && !empty($_POST['register_email']) && !empty($_POST['register_password'])) {
        $register_name = $conn->real_escape_string($_POST['register_name']);
        $register_email = $conn->real_escape_string($_POST['register_email']);
        $register_password = $conn->real_escape_string($_POST['register_password']);

        // 检查用户是否已存在
        $sql = "SELECT * FROM users WHERE email='$register_email'";
        $result = $conn->query($sql);// 执行查询

        if ($result->num_rows > 0) {
            // 如果用户已存在，设置消息
            $message = "User already exists with this email.";
        } else {
            // 如果用户不存在，插入新用户
            $sql = "INSERT INTO users (username, email, password, role) VALUES ('$register_name', '$register_email', '$register_password', 0)";
            // 插入成功，设置成功消息
            if ($conn->query($sql) === TRUE) {
                $message = "success";
            } else {// 插入失败，设置错误消息
                $message = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
        // 重定向到 login.php 并传递消息
        header("Location: login.php?message=" . urlencode($message));
        exit();// 结束脚本执行
    }

    // 处理登录表单
    if (isset($_POST['login']) && !empty($_POST['login_name']) && !empty($_POST['login_password'])) {
        $login_name = $conn->real_escape_string($_POST['login_name']);// 获取并转义登录用户名
        $login_password = $conn->real_escape_string($_POST['login_password']);// 获取并转义登录密码
        
        // 查询用户
        $sql = "SELECT * FROM users WHERE username='$login_name' AND password='$login_password'";
        $result = $conn->query($sql);// 执行查询

        if ($result->num_rows > 0) {
            // 获取用户信息
            $user = $result->fetch_assoc();
            
            // 登录成功，存储用户信息到Session
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['user_id']; // 存储 user_id
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // 重定向到 index.php
            header("Location: index.php");
            exit();
        } else {
            // 登录失败
            $message = "Invalid login credentials.";
            header("Location: login.php?message=" . urlencode($message));
            exit();
        }
    }
}

$conn->close();
?>
