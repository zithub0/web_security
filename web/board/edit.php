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
        'xss1_protection' => false,
        'xss2_protection' => false,
        'csrf_protection' => false,
        'sql_protection' => false
    ];
}

$settings = $_SESSION['security_settings'];
$csrf_protection = $settings['csrf_protection'];
$sql_protection = $settings['sql_protection'];

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

// 권한 체크 (작성자 본인이거나 관리자만 수정 가능)
if ($_SESSION['username'] !== $post['author'] && $_SESSION['role_name'] !== 'admin') {
    header("Location: view.php?id=" . $post_id);
    exit;
}

// 수정 처리
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF 대책
    if ($csrf_protection) {
        if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
            die('CSRF token validation failed.');
        }
    }

    $title = $_POST['title'];
    $content = $_POST['content'];
    
    if ($sql_protection) {
        $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $post_id);
        $stmt->execute();
    } else {
        $sql = "UPDATE posts SET title = '$title', content = '$content' WHERE id = $post_id";
        $conn->query($sql);
    }
    
    header("Location: view.php?id=" . $post_id);
    exit;
}

// CSRF 토큰 생성
if ($csrf_protection) {
    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));
    }
    $token = $_SESSION['token'];
} else {
    $token = '';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>게시글 수정</title>
</head>
<body>
    <h2>게시글 수정</h2>
    
    <form method="post" style="max-width: 600px;">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        
        <div style="margin-bottom: 15px;">
            <label for="title" style="display: block; margin-bottom: 5px; font-weight: bold;">제목:</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="content" style="display: block; margin-bottom: 5px; font-weight: bold;">내용:</label>
            <textarea id="content" name="content" rows="10" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>
        
        <div style="margin-top: 20px;">
            <input type="submit" value="수정 완료" style="background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">
            <a href="view.php?id=<?php echo $post_id; ?>" style="display: inline-block; padding: 10px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px;">취소</a>
        </div>
    </form>
</body>
</html>