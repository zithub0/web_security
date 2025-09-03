<?php
include_once('../includes/db.php');
include_once('../includes/auth.php');
session_start();

// 로그인 체크
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// 세션 기반 보안 설정 로드
if (!isset($_SESSION['security_settings'])) {
    $_SESSION['security_settings'] = [
        'sql_protection' => false
    ];
}

$sql_protection = $_SESSION['security_settings']['sql_protection'];

// 게시글 ID 가져오기
$post_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id <= 0) {
    header("Location: list.php");
    exit;
}

// 게시글 정보 가져오기
if ($sql_protection) {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM posts WHERE id = $post_id";
    $result = $conn->query($sql);
}

if ($result->num_rows == 0) {
    header("Location: list.php");
    exit;
}

$post = $result->fetch_assoc();

// 권한 체크 (작성자 본인이거나 관리자만 삭제 가능)
if ($_SESSION['username'] !== $post['author'] && $_SESSION['role_name'] !== 'admin') {
    header("Location: view.php?id=" . $post_id);
    exit;
}

// 게시글 삭제 (관련 댓글도 함께 삭제됨 - CASCADE 설정)
if ($sql_protection) {
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
} else {
    $sql = "DELETE FROM posts WHERE id = $post_id";
    $conn->query($sql);
}

// 삭제 후 목록으로 리다이렉트
header("Location: list.php?deleted=1");
exit;
?>