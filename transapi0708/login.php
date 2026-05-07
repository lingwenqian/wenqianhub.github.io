<!DOCTYPE html>
<html lang="en">

<head>
    <title>The Story of the Stone Knowledge Q&A Hub</title>

    <!-- Meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><!--设置视口，以适应移动设备 -->
    <meta http-equiv="X-UA-Compatible" content="ie=edge"><!--使IE浏览器以最新的渲染模式显示内容 -->

    <!-- google fonts -->
    <link href="//fonts.googleapis.com/css2?family=Jost:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- 引入外部CSS样式表 -->
    <link rel="stylesheet" href="login_style.css" type="text/css" media="all" />

    <!-- fontawesome v5 -->
    <script src="https://kit.fontawesome.com/af562a2a63.js" crossorigin="anonymous"></script>
    
    <!-- 包含一个JavaScript，用于在页面加载时显示消息并移除查询参数 -->
    <script>
        function getQueryParam(param) {
            let urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        function removeQueryParam(param) {
            let url = new URL(window.location);
            url.searchParams.delete(param);
            window.history.replaceState({}, document.title, url);
        }

        window.onload = function() {
            let message = getQueryParam('message');
            if (message === 'success') {
                alert('恭喜注册成功，请重新登录。');
                removeQueryParam('message');
            } else if (message) {
                alert(message);
                removeQueryParam('message');
            }
        }
    </script>
</head>

<body>

    <section class="forms">
        <div class="container">
            <!-- logo -->
            <div class="logo">
                <a class="brand-logo" href="login.php">The Story of the Stone Knowledge Q&A Hub</a>
            </div>
            <!-- //logo -->
            <div class="forms-grid">

                <!-- 登录 -->
                <div class="login">
                    <span class="fas fa-sign-in-alt"></span><!-- 登录图标 -->
                    <strong>Welcome!</strong>
                    <span>Sign in to your account</span>
                    <!-- 登录表单 -->
                    <form action="process.php" method="post" class="login-form">
                        <fieldset>
                            <div class="form">
                                <div class="form-row">
                                    <span class="fas fa-user"></span><!-- 用户图标 -->
                                    <label class="form-label" for="login_name">Name</label><!-- 用户名标签 -->
                                    <input type="text" id="login_name" name="login_name" class="form-text"><!-- 用户名输入框 -->
                                </div>
                                <div class="form-row">
                                    <span class="fas fa-eye"></span><!-- 密码图标 -->
                                    <label class="form-label" for="login_password">Password</label><!-- 密码标签 -->
                                    <input type="password" id="login_password" name="login_password" class="form-text"><!-- 密码输入框 -->
                                </div>
                                <div class="form-row button-login"><!-- 按钮行 -->
                                    <button type="submit" name="login" class="btn btn-login">Login <span class="fas fa-arrow-right"></span></button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                    <div class="signup-link"><!-- 注册链接 -->
                        <span>Don't have an account? <a href="sign_up.php">Sign up</a></span>
                    </div>
                </div>

            </div>

            <!-- copyright -->
            <div class="copy-right">
                <p>&copy; 2024 The Story of the Stone Knowledge Q&A Hub. All rights reserved | Design by CMSTeam</p>
            </div>
            <!-- //copyright -->
        </div>
    </section>

</body>

</html>
