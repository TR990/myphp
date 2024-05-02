<!DOCTYPE html>
<html>

<head>
    <title>文生图UI 测试1.0</title>
    <link rel="shortcut icon" href="V2.ico">
    <link rel="stylesheet" href="IK.css" type="text/css" />
</head>

<body>
    <div class="content">
        <div class="input-container">
            <h1>文生图UI 测试1.0</h1>
            <form action="" method="post">
                <label for="prompt">输入提示：</label>
                <input type="text" name="prompt" id="prompt" required><br><br>
                <label for="negative_prompt">输入负面提示：</label>
                <input type="text" name="negative_prompt" id="negative_prompt" required><br><br>
                <label for="secret_key">输入秘钥：</label>
                <input type="text" name="secret_key" id="secret_key" required><br><br>
                <input type="submit" name="generate" value="生成">
            </form>
            <div id="remaining-tasks">剩余任务数：
                <?php echo getAndUpdateTaskCount(0); ?>
                <br>
                <p style="color: red;">用户应遵守中华人民共和国的法律法规，尊重社会公德，不得利用本平台的AI绘图服务制作、传播或使用任何违法违规、侵权、色情、暴力、恐怖、诽谤、欺诈或其他有害的图形、图片等内容</p></p>
            </div>
        </div>
        <div class="image-container">
            <?php
            // 获取并更新用户的时间戳函数
            function getAndUpdateUserTimestamp($userId, $change) {
                $userTimestampFilePath = 'user_timestamps/' . $userId . '_timestamp.txt';

                if (file_exists($userTimestampFilePath)) {
                    $timestamp = intval(file_get_contents($userTimestampFilePath));
                } else {
                    $timestamp = 0;
                }

                // 更新时间戳
                $timestamp += $change;

                // 确保时间戳不会小于零
                if ($timestamp < 0) {
                    $timestamp = 0;
                }

                file_put_contents($userTimestampFilePath, $timestamp);

                return $timestamp;
            }

            // 获取并更新剩余任务数的函数
            function getAndUpdateTaskCount($change) {
                $countFilePath = 'task_count.txt';
                
                if (file_exists($countFilePath)) {
                    $count = intval(file_get_contents($countFilePath));
                } else {
                    $count = 0;
                }

                // 更新任务数
                $count += $change;

                // 确保任务数不会小于零
                if ($count < 0) {
                    $count = 0;
                }

                file_put_contents($countFilePath, $count);

                return $count;
            }

            // 添加秘钥验证功能
            if (isset($_POST['generate']) && $_POST['secret_key'] == '您的秘钥') {
                // 用户提交的秘钥与您设置的秘钥匹配
                // 执行生成操作
                $userId = 'user123'; // 替换为您的用户标识逻辑

                // 生成时增加任务数
                $remainingTasks = getAndUpdateTaskCount(1);

                // 调用API的函数
                function submit_post($url, $data) {
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    // 设置 Content-Type 头以指示 JSON 数据
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json'
                    ));

                    $response = curl_exec($ch);
                    curl_close($ch);

                    return array(
                        'response' => json_decode($response, true) // 注意传递 "true" 参数以解码为关联数组
                    );
                }

                // 保存编码后的图像
                function save_encoded_image($b64_image, $output_path) {
                    $image_data = base64_decode($b64_image);
                    file_put_contents($output_path, $image_data);
                }

                // 这里替换为您的API URL
                $txt2img_url = 'https://server.xiaofan.top/sdapi/v1/txt2img';

                // 这里替换为您的API请求数据
                $data = array(
                    'prompt' => $_POST['prompt'],
                    'negative_prompt' => $_POST['negative_prompt'],
                    'sampler_index' => 'DPM++ 2M Karras',
                    'seed' => -1,
                    'steps' => 20,
                    'width' => 512,
                    'height' => 768,
                    'cfg_scale' => 7.5,
                );

                $result = submit_post($txt2img_url, $data);

                if ($result['response']) {
                    if (isset($result['response']['images']) && is_array($result['response']['images']) && count($result['response']['images']) > 0) {
                        $save_image_path = 'tmp.png';
                        save_encoded_image($result['response']['images'][0], $save_image_path);

                        // 添加时间戳作为随机参数
                        $image_url = $save_image_path . '?' . time();
                        echo '请求成功~杰作↓';
                        echo '<br><img src="' . $image_url . '" alt="生成的图像" width="512" height="512">';
                    } else {
                        echo '在 API 响应中未找到有效的图像数据。';
                    }
                } else {
                    echo '可能站主没有开启哟调用失败或未收到响应。';
                }
                
                // 生成完成后减少任务数
                $remainingTasks = getAndUpdateTaskCount(-1);
            } else {
                // 秘钥不匹配或未提供秘钥
                echo '秘钥错误或未提供秘钥。';
            }
            ?>
        </div>       
    </div>

    <script>
        // 使用 JavaScript 更新剩余任务数
        function updateRemainingTasks(count) {
            document.getElementById('remaining-tasks').textContent = '剩余任务数：' + count;
        }
    </script>
</body>

</html>
