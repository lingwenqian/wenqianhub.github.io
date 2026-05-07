<?php
//开始会话，启用会话机制
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}
//检查用户是否已注册
//if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    //header("Location: process.php");
    //exit();
//}

// 获取用户 ID
$userId = $_SESSION['user_id'];
include 'fetch_sessions.php';
// 用户已登录，继续显示index.php内容
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation App</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" href="index_style.css" type="text/css" media="all" />
</head>
<body>
    <!-- 包含导航栏、侧边栏、内容区域和模态框 -->
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="#">Welcome!</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span><!--导航栏折叠按钮，在移动设备上显示-->
        </button>
        <div class="collapse navbar-collapse" id="navbarNav"><!-- 导航栏内容区域 -->
            <ul class="navbar-nav mr-auto"><!-- 导航栏左侧菜单项 -->
                <li class="nav-item">
                    <a class="nav-link" href="#" id="upload-link">Upload</a><!-- 上传文件 -->
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="knowledge-link">KnowledgeHub</a><!-- 知识中心 -->
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="aboutBtn">About Us</a>
                </li>
                
            </ul>
            <ul class="navbar-nav ml-auto"><!-- 导航栏右侧菜单项 -->
                <li class="nav-item">
                    <span class="navbar-text">
                        Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!<!-- 用户名 -->
                    </span>
                </li>
                <li class="nav-item">
                    <img src="user.jpg" class="rounded-circle" alt="User Avatar" width="40" height="40"><!-- 用户头像 -->
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php" id="logout-link">Logout</a><!-- 登出 -->
                </li>
            </ul>
        </div>
            <!-- 添加进度条，默认隐藏 -->
        <div id="progressContainer" class="progress" style="width: 100%; position: absolute; top: 56px; left: 0; display: none;">
            <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>
    </nav>

    <!-- About 弹窗 -->
    <div id="aboutModal" class="tc">
        <div class="tc-content">
            <span class="close">&times;</span>
            <h2>About Us</h2>
            <p>Our website can help you understand The Story of the Stone.</p>
            <p>For more information, contact us at: <a href="mailto:reddreamCMS@outlook.com">reddreamCMS@outlook.com</a></p>
        </div>
    </div>

    <!-- 侧边栏 -->
    <div id="sidebar">
        <div id="conversations"></div><!-- 动态填充会话列表 -->
        <div id="add-conversation">Add Conversation</div><!-- 添加会话按钮 -->
        <!-- 上传文件表单，默认隐藏 -->
        <form id="uploadForm" action="upload_txt.php" method="post" enctype="multipart/form-data" style="display: none;">
            <input type="file" id="txt_file" name="txt_file" class="form-control-file" required>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
    <!-- 内容区域 -->
    <div id="content">
        <div id="messages"></div><!-- 动态填充消息 -->
        <form id="question-form" class="d-flex align-items-center">
            <div class="dropdown dropup mr-2">
                <button type="button" id="file-select-button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Files</button>
                <div class="dropdown-menu p-3" aria-labelledby="file-select-button" id="fileCheckboxList">
                    <!-- 动态填充文件列表 -->
                </div>
            </div>
            <textarea id="text" class="form-control" placeholder="Ask a question" required></textarea>
            <button type="submit" class="btn btn-primary ml-2">Answer</button>
        </form>
    </div>

    <!-- 模态框结构，显示知识中心内容 -->
    <div class="modal fade" id="knowledgeHubModal" tabindex="-1" role="dialog" aria-labelledby="knowledgeHubModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document"><!-- 模态框对话框 -->
            <div class="modal-content"><!-- 模态框内容 -->
                <div class="modal-header"><!-- 模态框头部 -->
                    <h5 class="modal-title" id="knowledgeHubModalLabel">KnowledgeHub</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><!-- 关闭按钮 -->
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body"><!-- 模态框主体 -->
                    <div class="table-responsive" id="fileTableContainer">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="min-width: 150px;">File Name</th>
                                    <th style="min-width: 150px;">Time</th>
                                    <th style="min-width: 100px;">File Type</th>
                                    <th style="min-width: 100px;">Preview</th>
                                    <th style="min-width: 100px;">Delete</th>
                                </tr>
                            </thead>
                            <tbody id="fileTableBody">
                                <!-- 动态填充文件列表 -->
                            </tbody>
                        </table>
                    </div>
                    <div id="fileContentContainer" style="display: none;"><!-- 用于显示文件内容，初始状态为隐藏 -->
                        <pre id="fileContent" style="white-space: pre-wrap;"></pre>
                    </div>
                </div>
                <div class="modal-footer"><!-- 模态框底部 -->
                    <button type="button" class="btn btn-secondary" id="backToTable" style="display: none;">Back</button><!-- 返回表格视图（初始隐藏） -->
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><!-- 关闭模态框 -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            //处理文件上传
            $('#upload-link').on('click', function(event) {//点击上传链接时，模拟点击文件输入框
                event.preventDefault();
                $('#txt_file').click();
            });

            $('#txt_file').on('change', function() {//当文件选择发生变化时，自动提交表单
                $('#uploadForm').submit();
            });

            $('#uploadForm').on('submit', function(e) {//表单提交时，通过AJAX上传文件，并显示上传进度
                e.preventDefault(); // 阻止默认提交行为

                var formData = new FormData(this);

                $.ajax({
                    url: 'upload_txt.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = (evt.loaded / evt.total) * 100;
                                $('#progressBar').css('width', percentComplete + '%').attr('aria-valuenow', percentComplete).text(percentComplete.toFixed(2) + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        $('#progressBar').css('width', '100%').attr('aria-valuenow', '100').text('100%');
                        setTimeout(function() {
                            $('#progressContainer').hide();
                            $('#progressBar').css('width', '0%').attr('aria-valuenow', '0').text('0%');
                            var responseObj = typeof response === 'string' ? JSON.parse(response) : response;
                            if (responseObj.error) {
                                alert(responseObj.error); // 显示错误消息
                            } else {
                                alert(responseObj.success); // 显示成功消息
                            }
                        }, 500); // 延时500毫秒，确保进度条显示100%
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error occurred while uploading file: ' + textStatus + ' - ' + errorThrown);
                        $('#progressContainer').hide();
                        $('#progressBar').css('width', '0%').attr('aria-valuenow', '0').text('0%');
                    }
                });

                $('#progressContainer').show(); // 显示进度条
            });

    //文件预览和删除功能
    $('#knowledge-link').on('click', function(event) {//点击知识中心链接时，显示模态框并加载文件列表
        event.preventDefault();
        $('#knowledgeHubModal').modal('show');
        loadFiles();
    });

    $('#backToTable').on('click', function() {//点击返回按钮时，隐藏文件内容容器，显示文件表容器
        $('#fileContentContainer').hide();
        $('#fileTableContainer').show();
        $('#backToTable').hide();
    });

    function loadFiles() {//加载文件列表，并为每个文件添加预览和删除按钮的事件监听器
    $.ajax({
        url: 'fetch_files.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var files = response.files;
                var fileTableBody = $('#fileTableBody');
                fileTableBody.empty();
                files.forEach(function(file) {
                    var row = '<tr>' +
                        '<td>' + file.filename + '</td>' +
                        '<td>' + file.time + '</td>' +  // 确保这里是 time
                        '<td>' + file.filetype + '</td>' +
                        '<td><button class="btn btn-primary btn-sm preview-btn" data-filename="' + file.filename + '" data-time="' + file.time + '">Preview</button></td>' +  // 确保这里是 time
                        '<td><button class="btn btn-danger btn-sm delete-btn" data-filename="' + file.filename + '" data-time="' + file.time + '">Delete</button></td>' +  // 确保这里是 time
                        '</tr>';
                    fileTableBody.append(row);
                });

                $('.preview-btn').on('click', function() {//点击预览按钮时，加载并显示文件内容
                    var filename = $(this).data('filename');
                    var filetime = $(this).data('time');
                    console.log('Preview file:', filename, filetime);  // 添加调试信息
                    previewFile(filename, filetime);
                });

                $('.delete-btn').on('click', function() {//点击删除按钮时，删除文件并重新加载文件列表
                    var filename = $(this).data('filename');
                    var filetime = $(this).data('time');
                    if (confirm('Are you sure you want to delete this file?')) {
                        deleteFile(filename, filetime);
                    }
                });
            } else {
                alert('Failed to load files: ' + response.error);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('Error occurred while loading files: ' + textStatus + ' - ' + errorThrown);
            console.log('Error details:', jqXHR.responseText);
        }
    });
}

function previewFile(filename, filetime) {
    $.ajax({
        url: 'get_file_content.php',
        type: 'POST',
        data: { filename: filename, time: filetime },  // 确保参数名一致
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#fileContent').text(response.content);
                $('#fileTableContainer').hide();
                $('#fileContentContainer').show();
                $('#backToTable').show();
            } else {
                alert('Failed to load file content: ' + response.error);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('Error occurred while loading file content: ' + textStatus + ' - ' + errorThrown);
            console.log('Error details:', jqXHR.responseText);
        }
    });
}

    function deleteFile(filename, filetime) {
        $.ajax({
            type: "POST",
            url: "delete_file.php",
            data: { filename: filename, time: filetime },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    alert('File deleted successfully');
                    loadFiles(); // 重新加载文件列表
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
            alert('Error occurred while loading file content: ' + textStatus + ' - ' + errorThrown);
            console.log('Error details:', jqXHR.responseText);
        }
        });
    }
});

    </script>

    <script> //会话管理和消息显示功能
        //初始化会话数据
        var conversations = <?php echo json_encode($sessions); ?>;
        var currentConversationId = null;//跟踪当前选中的会话
        //循环 conversations 对象，渲染每个会话，并为每个会话添加删除按钮和点击事件
        function renderConversations() {
            $('#conversations').html('');
            for (var id in conversations) {
                var conversation = $('<div class="conversation"></div>')
                    .text(conversations[id].topic)
                    .data('id', id);
                if (id == currentConversationId) {
                    conversation.addClass('active');
                }
                
                var deleteButton = $('<button type="button" class="close delete-conversation" aria-label="Close" style="margin-left: 10px;"><span aria-hidden="true">&times;</span></button>')
                    .data('id', id)
                    .click(function() {
                        var sessionId = $(this).data('id');
                        if (confirm('Are you sure you want to delete this conversation?')) {
                            $.ajax({
                                url: 'delete_conversation.php',
                                type: 'POST',
                                data: JSON.stringify({ session_id: sessionId }),
                                contentType: 'application/json',
                                success: function(response) {
                                    var data = JSON.parse(response);
                                    if (data.success) {
                                        delete conversations[sessionId];
                                        if (currentConversationId == sessionId) {
                                            currentConversationId = null;
                                            $('#messages').html('');
                                        }
                                        renderConversations();
                                    } else {
                                        alert('Failed to delete conversation: ' + data.error);
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    alert('Error occurred while deleting conversation: ' + textStatus + ' - ' + errorThrown);
                                }
                            });
                        }
                    });
                conversation.append(deleteButton);
                $('#conversations').append(conversation);
            }
        }
        //根据当前选中的会话渲染消息列表
        // 修改 renderMessages 函数，支持展开和收起知识库内容
        function renderMessages() {
            $('#messages').html('');

            // 根据字符类型截取文本的函数
            function getShortText(text, length) {
                var isChinese = /[\u4e00-\u9fa5]/.test(text);
                if (isChinese) {
                    // 中文字符，截取前 length 个字符
                    return text.substring(0, length);
                } else {
                    // 英文单词，截取前 length 个单词
                    var words = text.split(/\s+/);
                    return words.slice(0, length).join(' ');
                }
            }
            if (currentConversationId !== null) {
                var messages = conversations[currentConversationId].messages;
                for (var i = 0; i < messages.length; i++) {
                    var messageClass = messages[i].type == 'user' ? 'user-message' : messages[i].type == 'api' ? 'api-message' : 'k-message';
                    var containerClass = messages[i].type == 'user' ? 'user-message-container' : messages[i].type == 'api' ? 'api-message-container' : 'k-message-container';
                    var avatarText = messages[i].type == 'user' ? 'YOU' : messages[i].type == 'api' ? 'AI' : 'K';

                    var messageContent = '<div class="message ' + messageClass + '">' + messages[i].text.replace(/\n/g, '<br>') + '</div>';
                    var expandButton = '';
                    var collapseButton = '';

                    if (messages[i].type == 'k') {
                        var fullText = messages[i].text; // 完整的知识库内容
                        // 根据字符类型截取文本
                        var shortText = getShortText(fullText, 100) + '......'; // 初始显示部分内容
                        messageContent = '<div class="message ' + messageClass + '">' + shortText.replace(/\n/g, '<br>') + '</div>';
                        expandButton = '<button type="button" class="expand-btn"></button>';
                        collapseButton = '<button type="button" class="collapse-btn" style="display:none;"></button>';
                    }

                    var avatarContent = '<div class="avatar">' + avatarText + '</div>';

                    if (messages[i].type == 'user') {
                        $('#messages').append('<div class="message-container ' + containerClass + '">' + messageContent + avatarContent + expandButton + collapseButton + '</div>');
                    } else {
                        $('#messages').append('<div class="message-container ' + containerClass + '">' + avatarContent + messageContent + expandButton + collapseButton + '</div>');
                    }
                }

                // 添加展开和收起按钮的点击事件
                $('#messages').on('click', '.expand-btn', function() {
                    $(this).hide().siblings('.collapse-btn').show(); // 隐藏展开按钮，显示收起按钮
                    var messageElement = $(this).parent().find('.message');
                    var fullText = conversations[currentConversationId].messages[$(this).parent().index()].text;
                    messageElement.html(fullText.replace(/\n/g, '<br>')); // 显示完整内容
                });

                $('#messages').on('click', '.collapse-btn', function() {
                    $(this).hide().siblings('.expand-btn').show(); // 隐藏收起按钮，显示展开按钮
                    var messageElement = $(this).parent().find('.message');
                    var fullText = conversations[currentConversationId].messages[$(this).parent().index()].text;
                    var shortText = getShortText(fullText, 100) + '......';
                    messageElement.html(shortText.replace(/\n/g, '<br>')); // 显示部分内容
                });
            }
        }


        $(document).ready(function(){
            renderConversations();//初始化并渲染会话列表
            //绑定点击事件到添加会话按钮
            $('#add-conversation').click(function(){
                var topic = prompt('Please enter the topic for the new conversation:');
                if (topic === null || topic.trim() === '') {//检查输入是否有效
                    return; // 用户取消或输入为空时，不继续处理
                }
                var userId; // 获取当前登录用户的ID
                //添加新的会话
                $.ajax({
                    url: 'add_conversation.php',
                    type: 'POST',
                    data: { topic: topic, user_id: userId },
                    success: function(response) {//处理成功响应，更新会话列表和当前会话
                        var data = JSON.parse(response);
                        if (data.success) {
                            var newId = data.session_id;
                            conversations[newId] = { topic: topic, messages: [] };
                            currentConversationId = newId;
                            renderConversations();
                            renderMessages();
                        } else {
                            alert('Failed to add conversation.');
                        }
                    },
                    error: function() {//处理错误响应
                        alert('Error occurred while adding conversation.');
                    }
                });
            });
            //切换会话
            $(document).on('click', '.conversation', function() {
                currentConversationId = $(this).data('id');//更新当前会话 ID
                renderConversations();
                renderMessages();
            });
            //初始化选中的文件数组
            var selectedFiles = [];
            function loadFileCheckboxList() {
                $.ajax({
                    url: 'fetch_files.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {//处理成功响应，动态生成复选框
                            var files = response.files;
                            var fileCheckboxList = $('#fileCheckboxList');
                            fileCheckboxList.empty();
                            files.forEach(function(file) {
                                var checkbox = '<div class="form-check">' +
                                    '<input class="form-check-input file-checkbox" type="checkbox" value="' + file.filename + '" id="file-' + file.filename + '">' +
                                    '<label class="form-check-label" for="file-' + file.filename + '">' + file.filename + '</label>' +
                                    '</div>';
                                fileCheckboxList.append(checkbox);
                            });

                            // 添加监听事件
                            $('.file-checkbox').on('change', function() {//选择所有具有file-checkbox类的复选框元素,添加一个事件监听器，当复选框的状态（勾选或取消勾选）发生变化时，将触发指定的回调函数
                                var filename = $(this).val();//获取当前触发事件的复选框的值
                                if ($(this).is(':checked')) {//检查当前复选框是否被勾选
                                    if (!selectedFiles.includes(filename)) {//检查selectedFiles数组中是否已经包含当前复选框的值
                                        selectedFiles.push(filename);//将当前复选框的值（filename）添加到selectedFiles数组的末尾
                                    }
                                } else {//如果当前复选框没有被勾选
                                    var index = selectedFiles.indexOf(filename);//在selectedFiles数组中查找当前复选框的值（filename），并返回其索引。如果数组中不包含此值，则返回-1
                                    if (index > -1) {//如果数组中包含此文件
                                        selectedFiles.splice(index, 1);//从selectedFiles数组中移除当前复选框的值
                                    }
                                }
                            });
                        } else {
                            alert(response.error);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {//处理错误响应
                        alert('Error occurred while loading files: ' + textStatus + ' - ' + errorThrown);
                        console.log('Error details:', jqXHR.responseText);
                    }
                });
            }
            
            // 显示文件复选框列表
            $('#file-select-button').on('click', function() {
                var fileCheckboxList = $('#fileCheckboxList');
                var button = $(this);
                fileCheckboxList.toggle();//切换复选框列表的显示或隐藏状态
                var expanded = fileCheckboxList.is(':visible');
                button.attr('aria-expanded', expanded);
                if (expanded) {
                    button.addClass('expanded'); // 添加类 'expanded'
                    loadFileCheckboxList(); // 如果复选框列表可见，调用函数加载复选框列表
                } else {
                    button.removeClass('expanded'); // 移除类 'expanded'
                }
            });

            // 点击窗口隐藏文件复选框列表
            window.onclick = function(event) {
                var fileCheckboxList = $('#fileCheckboxList');
                var fileSelectButton = $('#file-select-button');
                if (!fileCheckboxList.is(event.target) && !fileSelectButton.is(event.target) && fileCheckboxList.has(event.target).length === 0) {
                    fileCheckboxList.hide();
                    fileSelectButton.attr('aria-expanded', 'false');
                    fileSelectButton.removeClass('expanded'); // 移除类 'expanded'
                }
            };

            // 阻止点击复选框列表本身时关闭
            $('#fileCheckboxList').on('click', function(event) {
                event.stopPropagation();
            });

            //提交问题
            $('#question-form').submit(function(event){
                event.preventDefault();//阻止默认的表单提交行为
                if (currentConversationId === null) {//检查是否有选中的会话
                    alert('Please add a conversation first.');
                    return;
                }
                var text = $('#text').val();//获取用户输入的问题文本
                var selectedFiles = [];
                $('.file-checkbox:checked').each(function() {//获取选中的文件名
                    selectedFiles.push($(this).val());//将它们的值（即文件名）添加到 selectedFiles 数组中
                });
                //将用户消息添加到当前会话的消息列表中
                conversations[currentConversationId].messages.push({type: 'user', text: text});
                renderMessages();//渲染消息列表

                // 显示 "思考中，请稍等......"
                // alert("思考中，请稍等......")
                // $('#messages').append('<div id="thinking-message" class="message-container system-message-container"><div class="system-message">思考中，请稍等......</div></div>');
                
                var thinkingMessage = $('<div class="message-container system-message-container"><div class="system-message">思考中，请稍等......</div></div>');
                $('#messages').append(thinkingMessage);

                $.ajax({
                    url: 'find_similar_sentences.php',
                    type: 'POST',
                    dataType: 'json',//期望从服务器返回的数据类型是 json，这样 jQuery 在收到响应后会自动将其解析为 JavaScript 对象。
                    data: JSON.stringify({text: text, session_id: currentConversationId, files: selectedFiles}),//selectedFiles（选中的文件列表）
                    contentType: 'application/json',//确保数据以 JSON 格式发送到服务器
                    success: function(response) {//处理成功响应，更新消息列表
                        var data = response;
                        console.log('AI Answer:', data.answer); // 添加调试信息
                        console.log('Knowledge:', data.knowledge); // 添加调试信息

                        // 移除 "思考中，请稍等......" 消息
                        //  $('#thinking-message').remove();
                        thinkingMessage.remove();

                        conversations[currentConversationId].messages.push({type: 'api', text: data.answer});
                        
                        if (data.knowledge && data.knowledge.length > 0) {
                            conversations[currentConversationId].messages.push({type: 'k', text: data.knowledge});
                            // console.log("匹配到了相关知识")
                            renderMessages();
                        } else {
                            renderMessages();
                            // 如果知识库未匹配到相关知识，显示提示消息
                            // console.log("知识库里未匹配到相关知识")
                            var noKnowledgeMessage = $('<div class="message-container system-message-container"><div class="system-message">知识库里未匹配到相关知识</div></div>');
                            $('#messages').append(noKnowledgeMessage);
                        }

            
                        /*if (data.knowledge) {//如果返回的数据中包含知识库信息 
                            data.knowledge.forEach(function(knowledgeItem) {
                                conversations[currentConversationId].messages.push({type: 'k', text: knowledgeItem.text});
                            });*/
                        
                      
                    },
                    error: function() {//处理错误响应，显示错误消息
                        conversations[currentConversationId].messages.push({type: 'api', text: 'Error occurred while getting answer.'});
                        renderMessages();
                    }
                });
                $('#text').val('');//在请求发送后，清空用户输入框中的文本，以便用户继续输入
            });
            //自动调整输入框高度
            $('#text').on('input', function () {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });

        // 获取弹窗
        var modal = document.getElementById("aboutModal");

        // 获取按钮
        var btn = document.getElementById("aboutBtn");

        // 获取 <span> 元素，用于关闭弹窗
        var span = document.getElementsByClassName("close")[0];

        // 当用户点击按钮时，打开弹窗
        btn.onclick = function() {
            modal.style.display = "block";
        }

        // 当用户点击 <span> (x)，关闭弹窗
        span.onclick = function() {
            modal.style.display = "none";
        }

        // 当用户点击窗口任何地方，关闭弹窗
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>


</body>
</html>
