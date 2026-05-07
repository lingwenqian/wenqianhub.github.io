<!DOCTYPE html>
<html lang="zxx">

<head>
    <title>The Story of the Stone Knowledge Q&A Hub</title>

    <!-- Meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- google fonts -->
    <link href="//fonts.googleapis.com/css2?family=Jost:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- CSS Stylesheet -->
    <link rel="stylesheet" href="login_style.css" type="text/css" media="all" />

    <!-- fontawesome v5 -->
    <script src="https://kit.fontawesome.com/af562a2a63.js" crossorigin="anonymous"></script>
    
    <!-- JavaScript for displaying alerts -->
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

                <!-- 注册 -->
                <div class="register">
                    <span class="fas fa-user-circle"></span>
                    <strong>Create account!</strong>
                    <form action="process.php" method="post" class="register-form">
                        <fieldset>
                            <div class="form">
                                <div class="form-row">
                                    <span class="fas fa-user"></span>
                                    <label class="form-label" for="register_name">Name</label>
                                    <input type="text" id="register_name" name="register_name" class="form-text">
                                </div>
                                <div class="form-row">
                                    <span class="fas fa-envelope"></span>
                                    <label class="form-label" for="register_email">E-mail</label>
                                    <input type="email" id="register_email" name="register_email" class="form-text">
                                </div>
                                <div class="form-row">
                                    <span class="fas fa-lock"></span>
                                    <label class="form-label" for="register_password">Password</label>
                                    <input type="password" id="register_password" name="register_password" class="form-text">
                                </div>
                                <div class="form-row button-login">
                                    <button type="submit" name="register" class="btn btn-login">Create <span class="fas fa-arrow-right"></span></button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
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
