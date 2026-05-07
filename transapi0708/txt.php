<!--上传TM的页面-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--网页标签标题-->
    <title>Upload TXT File</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!--这个区块构建了网页的基本结构，包括：网页标题、上传txt文件的表单、进度条容器-->
    <div class="container">
        <!--设置网页大标题-->
        <h1 class="mt-5">Upload TXT File</h1>
        <!--上传文件的表单，使用POST方法提交到upload_txt.php， enctype为multipart/form-data表示支持文件上传-->
        <form id="uploadForm" action="upload_txt.php" method="post" enctype="multipart/form-data">
            <!--选择要上传的文件-->
            <div class="form-group">
                <label for="txt_file">Select TXT File:</label>
                <input type="file" id="txt_file" name="txt_file" class="form-control-file" required>
            </div>
            <!--提交按钮-->
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
        <!--返回首页-->
        <form id="return" action="index.php" method="get">
            <button type="submit">Return</button>
        </form>
        <!--进度条容器，初始状态hide-->
        <div id="progressContainer" class="mt-4" style="display: none;">
            <h3>Processing Progress</h3>
            <div id="progressBar" class="progress">
                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
            <!--进度信息-->
            <div id="progressMessages" class="mt-3"></div>
        </div>
    </div>

    <!--导入jQuery库来编写JS脚本，实现提交表单时的异步请求-->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        //当文档加载完成，将执行后续函数，防止元素还没有加载完就对其进行操作
        $(document).ready(function() {
            //为id为uploadForm的表单添加监听器，当表单提交时将执行后续函数
            $('#uploadForm').submit(function(e) {
                //阻止表单的默认提交行为，而是用JS来处理提交，避免因此刷新网页
                e.preventDefault();
                //创建FormData对象，数据来自于当前表单，通过this引用，用于发送表单数据
                var formData = new FormData(this);

                //使用 $.ajax() 函数发送一个 POST 请求到服务器端的 upload_txt.php文件，请求的数据为表单数据。
                $.ajax({
                    url: 'upload_txt.php',
                    type: 'POST',
                    data: formData,
                    processData: false, // 不对数据进行处理
                    contentType: false, // 不设置内容类型
                    //创建XMLHttpRequest对象，设置上传进度的监听器
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        //给XMLHttpRequest对象的上传事件添加了一个监听器。这个监听器在上传过程中触发，用于跟踪上传进度。
                        xhr.upload.addEventListener('progress', function(evt) {
                            //检查上传的进度是否可计算
                            if (evt.lengthComputable) {
                                //如果进度可计算，计算上传的百分比
                                var percentComplete = (evt.loaded / evt.total) * 100;
                                // 更新进度条的显示，使用jQuery选择器找到进度条元素，设置其宽度和aria-valuenow属性，并更新显示的文本为上传百分比（保留两位小数）
                                $('#progressBar .progress-bar').css('width', percentComplete + '%').attr('aria-valuenow', percentComplete).text(percentComplete.toFixed(2) + '%');
                            }
                        }, false);//false表示事件监听器在事件冒泡阶段执行（先触发目标元素、内部元素）
                        return xhr;
                    },
                    //请求成功，进度信息区域显示response
                    success: function(response) {
                        $('#progressMessages').append('<p>' + response + '</p>');
                    },
                    //请求失败，弹出错误信息
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error uploading file: ' + textStatus);
                    }
                });
                //显示进度条（一开始是hide）
                $('#progressContainer').show();
            });
        });
    </script>
</body>
</html>