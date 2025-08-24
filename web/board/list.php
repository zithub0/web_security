<?php


//XSS 대책1 : 응답의 문자 인코딩 지정
if ($xss1_protection) {
    header("Content-Type: text/html; charset=UTF-8");
}



include_once('../includes/db.php');
session_start();

// 세션 기반 보안 설정 로드
if (!isset($_SESSION['security_settings'])) {
    $_SESSION['security_settings'] = [
        'xss1_protection' => false,
        'xss2_protection' => false,
        'csrf1_protection' => false,
        'csrf2_protection' => false,
        'sql_protection' => false
    ];
}

$settings = $_SESSION['security_settings'];
$xss1_protection = $settings['xss1_protection'];
$xss2_protection = $settings['xss2_protection'];
$csrf1_protection = $settings['csrf1_protection'];
$csrf2_protection = $settings['csrf2_protection'];
$sql_protection = $settings['sql_protection'];


// 페이지네이션 설정 (목록 전용)
$posts_per_page = 10; // 페이지당 10개 게시글
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

// 전체 게시글 수 가져오기
$count_sql = "SELECT COUNT(*) as total FROM board";
$count_result = $conn->query($count_sql);
$total_posts = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $posts_per_page);

// 현재 페이지의 게시글 가져오기 (최신순 정렬)
$sql = "SELECT * FROM board ORDER BY created_at DESC LIMIT $posts_per_page OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>게시판 목록</title>
</head>
<body>
    <h2>게시판 목록</h2>
    
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
    <div style="margin-bottom: 20px; text-align: right;">
        <a href="write.php" style="background-color: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">✏️ 새 글 작성</a>
    </div>
    <?php endif; ?>

    <hr>

    <?php
    if ($result->num_rows > 0) {
        // 게시글 목록 표시
        echo "<h3>최신 게시글 목록</h3>";
        echo "<ul style='list-style-type: none; padding: 0;'>";
        $post_number = ($page - 1) * 10 + 1;
        while($row = $result->fetch_assoc()) {
            echo "<li style='border: 1px solid #ddd; padding: 10px; margin: 5px 0; background-color: #f9f9f9;'>";
            echo "<strong>" . $post_number . ". ";
            
            // XSS 대책2 : HTML 엔티티 인코딩
            if ($xss2_protection) {
                echo htmlspecialchars($row["username"]) . "</strong> - ";
                echo htmlspecialchars(substr($row["content"], 0, 50)) . (strlen($row["content"]) > 50 ? "..." : "");
            } else {
                echo $row["username"] . "</strong> - ";
                echo substr($row["content"], 0, 50) . (strlen($row["content"]) > 50 ? "..." : "");
            }
            echo " <a href='view.php?id=" . $row["id"] . "' style='color: #007cba; text-decoration: none;'>[상세보기]</a>";
            echo "<br><small style='color: #666;'>작성일: " . $row["created_at"] . "</small>";
            echo "</li>";
            $post_number++;
        }
        echo "</ul>";
    } else {
        echo "<p>게시글이 없습니다.</p>";
    }
    ?>

    <!-- 페이지네이션 네비게이션 -->
    <?php if ($total_pages > 1): ?>
    <div style="margin: 20px 0; text-align: center;">
        <!-- 이전 페이지 -->
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" style="text-decoration: none; margin: 0 5px; padding: 5px 10px; border: 1px solid #ccc;">이전</a>
        <?php endif; ?>
        
        <!-- 페이지 번호들 -->
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i == $page): ?>
                <span style="margin: 0 5px; padding: 5px 10px; background-color: #007cba; color: white; border: 1px solid #007cba;"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?page=<?php echo $i; ?>" style="text-decoration: none; margin: 0 5px; padding: 5px 10px; border: 1px solid #ccc;"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <!-- 다음 페이지 -->
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>" style="text-decoration: none; margin: 0 5px; padding: 5px 10px; border: 1px solid #ccc;">다음</a>
        <?php endif; ?>
    </div>
    
    <p style="text-align: center; color: #666;">
        페이지 <?php echo $page; ?> / <?php echo $total_pages; ?> (총 <?php echo $total_posts; ?>개 게시글)
    </p>
    <?php endif; ?>
</body>
</html>