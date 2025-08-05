<?php
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    // This is vulnerable to directory traversal
    include($file);
}
?>

// 디렉토리 트레버셜 공격 예시 1
// http://localhost/file_viewer.php?file=../../../../../../etc/passwd
// 해당 코드는 도커 안에 존재