<?php
include_once('../includes/db.php');
include_once('../includes/auth.php');
session_start();

// 세션 기반 보안 설정 로드
if (!isset($_SESSION['security_settings'])) {
    $_SESSION['security_settings'] = [
        'xss1_protection' => false,
        'xss2_protection' => false,
        'csrf_protection' => false,
        'sql_protection' => false,
        'search_sql_protection' => false
    ];
}

$settings = $_SESSION['security_settings'];
$xss1_protection = $settings['xss1_protection'];
$xss2_protection = $settings['xss2_protection'];
$csrf_protection = $settings['csrf_protection'];
$sql_protection = $settings['sql_protection'];

//XSS 대책1 : 응답의 문자 인코딩 지정
if ($xss1_protection) {
    header("Content-Type: text/html; charset=UTF-8");
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // CSRF 대책: POST 요청 시 토큰 검증
        if ($csrf_protection) {
            if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
                die('CSRF token validation failed.');
            }
        }

        $username = $_SESSION['username'];
        
        
        // 댓글 작성
        if (isset($_POST['comment']) && isset($_POST['post_id'])) {
            $comment = $_POST['comment'];
            $post_id = (int)$_POST['post_id'];
            
            if ($sql_protection) {
                // SQL 인젝션 대책: Prepared Statements 사용
                $stmt = $conn->prepare("INSERT INTO comments (post_id, username, content) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $post_id, $username, $comment);
                $stmt->execute();
            } else {
                // SQL Injection 취약
                $sql = "INSERT INTO comments (post_id, username, content) VALUES ('$post_id', '$username', '$comment')";
                $conn->query($sql);
            }
            // 현재 페이지로 리다이렉트 (GET 파라미터 유지)
            $current_params = $_SERVER['QUERY_STRING'];
            header("Location: view.php?" . $current_params);
            exit;
        }
    }
}

// 게시글 ID 가져오기
$post_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id <= 0) {
    header("Location: list.php");
    exit;
}

// 특정 게시글 가져오기
if ($sql_protection) {
    // SQL 인젝션 대책: Prepared Statements 사용
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // SQL Injection 취약
    $sql = "SELECT * FROM posts WHERE id = $post_id";
    $result = $conn->query($sql);
}

if ($result->num_rows == 0) {
    header("Location: list.php");
    exit;
}

$post = $result->fetch_assoc();

// 해당 게시글의 댓글들 가져오기
$comments_sql = "SELECT * FROM comments WHERE post_id = $post_id ORDER BY created_at ASC";
$comments_result = $conn->query($comments_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>게시글 상세보기</title>
    <style>
        /* 게시글 상세보기 스타일 */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <h2>게시글 상세보기</h2>

    <!-- 게시글 상세 내용 -->
    <div style='border: 1px solid #ccc; padding: 20px; margin: 10px 0; background-color: #fff;'>
        <h3 style='margin-top: 0;'><?php echo htmlspecialchars($post['title']); ?></h3>
        <p><strong>작성자:</strong> <?php echo htmlspecialchars($post['author']); ?></p>
        <p><strong>작성일:</strong> <?php echo $post['created_at']; ?></p>
        <hr>
        <div style='line-height: 1.6;'>
            <?php if ($xss2_protection): ?>
                <?php //XSS 대책2 : htmlspecialchars 적용 ?>
                <?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?>
            <?php else: ?>
                <?php echo $post['content']; ?> <!-- XSS -->
            <?php endif; ?>
        </div>
    </div>

    <!-- 네비게이션 버튼들 -->
    <div style="margin: 20px 0;">
        <a href="list.php" style="display: inline-block; margin-right: 10px; padding: 8px 16px; background-color: #007cba; color: white; text-decoration: none; border-radius: 4px;">목록으로 돌아가기</a>
        
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <?php if ($_SESSION['username'] === $post['author'] || $_SESSION['role_name'] === 'admin'): ?>
                <a href="edit.php?id=<?php echo $post['id']; ?>" style="display: inline-block; margin-right: 10px; padding: 8px 16px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px;">✏️ 수정</a>
                <a href="delete.php?id=<?php echo $post['id']; ?>" style="display: inline-block; margin-right: 10px; padding: 8px 16px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 4px;" onclick="return confirm('정말로 이 게시글을 삭제하시겠습니까?');">🗑️ 삭제</a>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php
        // 이전 게시글 찾기
        if ($sql_protection) {
            $prev_stmt = $conn->prepare("SELECT id FROM posts WHERE id < ? ORDER BY id DESC LIMIT 1");
            $prev_stmt->bind_param("i", $post_id);
            $prev_stmt->execute();
            $prev_result = $prev_stmt->get_result();
        } else {
            $prev_sql = "SELECT id FROM posts WHERE id < $post_id ORDER BY id DESC LIMIT 1";
            $prev_result = $conn->query($prev_sql);
        }
        
        if ($prev_result->num_rows > 0) {
            $prev_post = $prev_result->fetch_assoc();
            echo "<a href='view.php?id=" . $prev_post['id'] . "' style='margin-right: 10px; padding: 8px 16px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px;'>이전 게시글</a>";
        }
        
        // 다음 게시글 찾기
        if ($sql_protection) {
            $next_stmt = $conn->prepare("SELECT id FROM posts WHERE id > ? ORDER BY id ASC LIMIT 1");
            $next_stmt->bind_param("i", $post_id);
            $next_stmt->execute();
            $next_result = $next_stmt->get_result();
        } else {
            $next_sql = "SELECT id FROM posts WHERE id > $post_id ORDER BY id ASC LIMIT 1";
            $next_result = $conn->query($next_sql);
        }
        
        if ($next_result->num_rows > 0) {
            $next_post = $next_result->fetch_assoc();
            echo "<a href='view.php?id=" . $next_post['id'] . "' style='padding: 8px 16px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px;'>다음 게시글</a>";
        }
        ?>
    </div>

    <!-- 댓글 섹션 -->
    <div style="margin: 30px 0; border-top: 2px solid #eee; padding-top: 20px;">
        <h3 style="color: #333;">댓글 (<?php echo $comments_result->num_rows; ?>개)</h3>
        
        <!-- 댓글 목록 -->
        <div style="margin-bottom: 20px;">
            <?php if ($comments_result->num_rows > 0): ?>
                <?php while($comment = $comments_result->fetch_assoc()): ?>
                    <div style="border: 1px solid #ddd; padding: 12px; margin: 8px 0; border-radius: 4px; background-color: #f9f9f9;">
                        <div style="margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: #007cba;"><?php echo $comment['username']; ?></strong>
                                <small style="color: #666; margin-left: 10px;"><?php echo $comment['created_at']; ?></small>
                            </div>
                            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                                <?php if ($_SESSION['username'] === $comment['username'] || $_SESSION['role_name'] === 'admin'): ?>
                                    <div style="font-size: 12px;">
                                        <a href="edit_comment.php?id=<?php echo $comment['id']; ?>&post_id=<?php echo $post_id; ?>" style="color: #28a745; text-decoration: none; margin-right: 10px;">수정</a>
                                        <a href="delete_comment.php?id=<?php echo $comment['id']; ?>&post_id=<?php echo $post_id; ?>" style="color: #dc3545; text-decoration: none;" onclick="return confirm('정말로 이 댓글을 삭제하시겠습니까?');">삭제</a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div style="line-height: 1.4;">
                            <?php if ($xss2_protection): ?>
                                <?php echo htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php else: ?>
                                <?php echo $comment['content']; ?> <!-- XSS 취약 -->
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #666; font-style: italic;">아직 댓글이 없습니다. 첫 번째 댓글을 작성해보세요!</p>
            <?php endif; ?>
        </div>

        <!-- 댓글 작성 폼 -->
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        <div style="border: 2px solid #007cba; padding: 15px; border-radius: 6px; background-color: #f0f8ff;">
            <h4 style="margin-top: 0; color: #007cba;">댓글 작성</h4>
            <form method="post">
                <?php
                // CSRF 대책: 폼에 숨겨진 토큰 추가 (댓글용)
                if ($csrf_protection) {
                    if (empty($_SESSION['token'])) {
                        $_SESSION['token'] = bin2hex(random_bytes(32));
                    }
                    $token = $_SESSION['token'];
                } else {
                    $token = '';
                }
                ?>
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                <textarea name="comment" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; resize: vertical;" placeholder="댓글을 입력하세요..."></textarea><br><br>
                <input type="submit" value="댓글 작성" style="background-color: #007cba; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">
            </form>
        </div>
        <?php else: ?>
        <p style="color: #666; text-align: center; font-style: italic;">
            댓글을 작성하려면 <a href="../login.php" style="color: #007cba;">로그인</a>해주세요.
        </p>
        <?php endif; ?>
    </div>
</body>
</html>