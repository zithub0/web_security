<?php
include_once('includes/db.php');
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);

    // --- 파일 업로드 보안 강화 기능 (현재 주석 처리되어 비활성화됨) ---
    // 이 코드는 업로드되는 파일의 유형을 제한하여 악성 파일 업로드를 방지합니다.
    // 연구 목적으로 기능을 확인하려면 아래 주석을 해제하십시오.

    /*
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
        echo "Sorry, only JPG, JPEG, PNG, GIF, TXT, PDF, DOC, DOCX files are allowed.";
        // 업로드 중단
        exit;
    }
    */
    // --- 파일 업로드 보안 강화 기능 끝 ---

    // Path Traversal
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        echo "The file ". htmlspecialchars( basename( $_FILES["file"]["name"])). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>File Upload</title>
</head>
<body>
    <h2>File Upload</h2>
    <form method="post" enctype="multipart/form-data">
        Select image to upload:
        <input type="file" name="file">
        <input type="submit" value="Upload Image" name="submit">
    </form>
</body>
</html>