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

// 댓글 ID와 게시글 ID 가져오기
$comment_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$post_id = isset($_GET['post_id']) && is_numeric($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

if ($comment_id <= 0 || $post_id <= 0) {
    header("Location: list.php");
    exit;
}

// 댓글 정보 가져오기
if ($sql_protection) {
    $stmt = $conn->prepare("SELECT * FROM comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM comments WHERE id = $comment_id";
    $result = $conn->query($sql);
}

if ($result->num_rows == 0) {
    header("Location: view.php?id=" . $post_id);
    exit;
}

$comment = $result->fetch_assoc();

// 권한 체크 (댓글 작성자 본인이거나 관리자만 삭제 가능)
if ($_SESSION['username'] !== $comment['username'] && $_SESSION['role_name'] !== 'admin') {
    header("Location: view.php?id=" . $post_id);
    exit;
}

// 댓글 삭제
if ($sql_protection) {
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
} else {
    $sql = "DELETE FROM comments WHERE id = $comment_id";
    $conn->query($sql);
}

// 삭제 후 게시글로 리다이렉트
header("Location: view.php?id=" . $post_id . "&comment_deleted=1");
exit;
?>