<?php
include_once('includes/db.php');
session_start();

// 세션 기반 보안 설정 로드
if (!isset($_SESSION['security_settings'])) {
    $_SESSION['security_settings'] = [
        'xss1_protection' => false,
        'xss2_protection' => false,
        'csrf1_protection' => false,
        'csrf2_protection' => false,
        'sql_protection' => false,
        'search_sql_protection' => false,
        'file_upload_protection' => false
    ];
}

$settings = $_SESSION['security_settings'];
$file_upload_protection = $settings['file_upload_protection'];

$is_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

if ($is_logged_in && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);

    // 파일 업로드 보안 강화 기능 (토글로 제어)
    if ($file_upload_protection) {
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_extensions = array("jpg", "jpeg", "png", "gif", "txt", "pdf", "doc", "docx"); // 허용할 확장자 목록

        // MIME 타입 검사 (더 강력한 보안을 위해)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['file']['tmp_name']);
        finfo_close($finfo);

        $allowed_mime_types = array(
            'image/jpeg',
            'image/png',
            'image/gif',
            'text/plain',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );

        if (!in_array($file_type, $allowed_extensions) || !in_array($mime_type, $allowed_mime_types)) {
            $upload_message = '<div class="error-message">❌  허용되지 않는 파일 형식입니다. JPG, JPEG, PNG, GIF, TXT, PDF, DOC, DOCX 파일만 업로드 가능합니다.<br>감지된 MIME 타입: ' . htmlspecialchars($mime_type) . '</div>';
        } else {
            // 파일 업로드 진행
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                $upload_message = '<div class="success-message">✅ 파일 "'. htmlspecialchars( basename( $_FILES["file"]["name"])). '"이 성공적으로 업로드되었습니다. (보안 검사 통과)</div>';
            } else {
                $upload_message = '<div class="error-message">❌ 파일 업로드 중 오류가 발생했습니다.</div>';
            }
        }
    } else {
        // 보안 검사 없이 파일 업로드 (취약한 상태)
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $upload_message = '<div class="success-message">✅ 파일 "'. htmlspecialchars( basename( $_FILES["file"]["name"])). '"이 성공적으로 업로드되었습니다. ⚠️ (보안 검사 비활성화 상태)</div>';
        } else {
            $upload_message = '<div class="error-message">❌ 파일 업로드 중 오류가 발생했습니다.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>파일 업로드</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
            border: none;
            cursor: pointer;
            display: inline-block;
            text-align: center;
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
        
        .btn-success {
            background-color: #28a745;
            color: white;
            font-size: 16px;
            padding: 12px 25px;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .upload-form {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 2px dashed #007cba;
            text-align: center;
        }
        
        .upload-form h3 {
            color: #007cba;
            margin-top: 0;
            margin-bottom: 20px;
        }
        
        .file-input-wrapper {
            position: relative;
            margin: 20px 0;
        }
        
        .file-input {
            display: none;
        }
        
        .file-input-label {
            display: inline-block;
            padding: 15px 30px;
            background-color: #e9ecef;
            color: #495057;
            border-radius: 5px;
            cursor: pointer;
            border: 2px solid #ced4da;
            transition: all 0.2s;
            margin-bottom: 15px;
        }
        
        .file-input-label:hover {
            background-color: #007cba;
            color: white;
            border-color: #007cba;
        }
        
        .file-info {
            margin: 15px 0;
            padding: 10px;
            background: #e3f2fd;
            border-radius: 5px;
            color: #1976d2;
            display: none;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
            margin-bottom: 20px;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #dc3545;
            margin-bottom: 20px;
        }
        
        .info-box {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007cba;
            margin-bottom: 20px;
        }
        
        .info-box h4 {
            color: #007cba;
            margin-top: 0;
        }
        
        .info-box p {
            margin-bottom: 10px;
            color: #333;
        }
    </style>
    <script>
        function updateFileName(input) {
            const fileInfo = document.getElementById('file-info');
            const fileName = document.getElementById('file-name');
            
            if (input.files.length > 0) {
                fileName.textContent = input.files[0].name;
                fileInfo.style.display = 'block';
            } else {
                fileInfo.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="nav-buttons">
            <a href="index.php" class="btn btn-secondary">🏠 메인으로</a>
            <a href="file_viewer.php" class="btn btn-primary">📁 파일 뷰어</a>
        </div>
        
        <div class="header">
            <h2>📤 파일 업로드</h2>
        </div>
        
        <?php if (isset($upload_message)): ?>
            <?php echo $upload_message; ?>
        <?php endif; ?>
        
        <?php if (!$is_logged_in): ?>
        <div style="border: 2px solid #ffc107; background-color: #fff3cd; padding: 20px; border-radius: 8px; text-align: center;">
            <h3 style="color: #856404; margin-top: 0;">🔒 로그인이 필요합니다</h3>
            <p style="color: #856404; margin-bottom: 20px;">파일 업로드 기능을 사용하려면 먼저 로그인해주세요.</p>
            <a href="login.php" class="btn btn-primary">로그인하기</a>
        </div>
        <?php else: ?>
        
        <div class="info-box">
            <h4>📋 업로드 안내</h4>
            <p>• 다양한 파일 형식을 업로드할 수 있습니다.</p>
            <p>• 업로드된 파일은 파일 뷰어에서 확인할 수 있습니다.</p>
            <p>• 이미지 파일과 텍스트 파일은 바로 미리볼 수 있습니다.</p>
        </div>
        
        <!-- 보안 상태 표시 -->
        <div style="<?php echo $file_upload_protection ? 'background: #d4edda; color: #155724; border: 2px solid #28a745;' : 'background: #f8d7da; color: #721c24; border: 2px solid #dc3545;'; ?> padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
            <h4 style="margin-top: 0;">
                <?php if ($file_upload_protection): ?>
                    🛡️ 파일 업로드 보안 활성화
                <?php else: ?>
                    ⚠️ 파일 업로드 보안 비활성화
                <?php endif; ?>
            </h4>
            <p style="margin-bottom: 0;">
                <?php if ($file_upload_protection): ?>
                    파일 확장자 및 MIME 타입 검사가 활성화되어 있습니다. 안전한 파일만 업로드됩니다.
                <?php else: ?>
                    <strong>위험:</strong> 모든 파일 타입의 업로드가 허용됩니다. 보안 설정에서 활성화를 권장합니다.
                <?php endif; ?>
                <br><small><a href="security.php" style="color: inherit; text-decoration: underline;">보안 설정 변경하기</a></small>
            </p>
        </div>
        
        <div class="upload-form">
            <h3>📂 파일 선택</h3>
            <form method="post" enctype="multipart/form-data">
                <div class="file-input-wrapper">
                    <input type="file" name="file" id="file" class="file-input" onchange="updateFileName(this)" required>
                    <label for="file" class="file-input-label">
                        📁 파일 선택하기
                    </label>
                </div>
                
                <div id="file-info" class="file-info">
                    선택된 파일: <strong id="file-name"></strong>
                </div>
                
                <button type="submit" name="submit" class="btn btn-success">
                    🚀 파일 업로드
                </button>
            </form>
        </div>
        
        <?php endif; ?>
    </div>
</body>
</html>