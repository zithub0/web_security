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
$search_sql_protection = $settings['search_sql_protection'];

//XSS 대책1 : 응답의 문자 인코딩 지정
if ($xss1_protection) {
    header("Content-Type: text/html; charset=UTF-8");
}


// 검색 기능 처리
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
$search_params = [];

if (!empty($search_query)) {
    if ($search_sql_protection) {
        // SQL 인젝션 대책: Prepared Statements 사용
        $search_condition = " WHERE title LIKE ? OR content LIKE ? OR author LIKE ?";
        $search_like = "%$search_query%";
        $search_params = [$search_like, $search_like, $search_like];
    } else {
        // 🚨 SQL Injection 취약점: 사용자 입력이 직접 SQL 쿼리에 삽입됨
        $search_condition = " WHERE title LIKE '%$search_query%' OR content LIKE '%$search_query%' OR author LIKE '%$search_query%'";
    }
}

// 페이지네이션 설정
$posts_per_page = 10; // 페이지당 10개 게시글
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

// 전체 게시글 수 가져오기 (검색 조건 포함)
if (!empty($search_query) && $search_sql_protection) {
    // Prepared Statement로 카운트 쿼리
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM posts" . $search_condition);
    $count_stmt->bind_param("sss", $search_params[0], $search_params[1], $search_params[2]);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
} else {
    // 기존 방식 (취약하거나 검색 없음)
    $count_sql = "SELECT COUNT(*) as total FROM posts" . $search_condition;
    $count_result = $conn->query($count_sql);
}

// SQL 쿼리 실행 실패 시 처리
if (!$count_result) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>SQL 오류가 발생했습니다!</h4>";
    echo "<p>오류 메시지: " . htmlspecialchars($conn->error) . "</p>";
    if (isset($count_sql)) {
        echo "<p>실행된 쿼리: " . htmlspecialchars($count_sql) . "</p>";
    }
    echo "<p><a href='list.php'>게시판 목록으로 돌아가기</a></p>";
    echo "</div>";
    exit;
}

$total_posts = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $posts_per_page);

// 현재 페이지의 게시글 가져오기 (검색 조건 포함, 최신순 정렬)
if (!empty($search_query) && $search_sql_protection) {
    // Prepared Statement로 메인 쿼리
    $main_stmt = $conn->prepare("SELECT * FROM posts" . $search_condition . " ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $main_stmt->bind_param("sssii", $search_params[0], $search_params[1], $search_params[2], $posts_per_page, $offset);
    $main_stmt->execute();
    $result = $main_stmt->get_result();
} else {
    // 기존 방식 (취약하거나 검색 없음)
    $sql = "SELECT * FROM posts" . $search_condition . " ORDER BY created_at DESC LIMIT $posts_per_page OFFSET $offset";
    $result = $conn->query($sql);
}

// 메인 쿼리 실행 실패 시 처리
if (!$result) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>SQL 오류가 발생했습니다!</h4>";
    echo "<p>오류 메시지: " . htmlspecialchars($conn->error) . "</p>";
    echo "<p>실행된 쿼리: " . htmlspecialchars($sql) . "</p>";
    echo "<p><a href='list.php'>게시판 목록으로 돌아가기</a></p>";
    echo "</div>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>게시판 목록</title>
</head>
<body>
    <h2>게시판 목록</h2>
    
    <div style="margin-bottom: 20px; text-align: right;">
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <a href="write.php" style="background-color: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">✏️ 새 글 작성</a>
        <?php else: ?>
            <a href="../login.php" style="background-color: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">✏️ 새 글 작성 (로그인 필요)</a>
        <?php endif; ?>
    </div>

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
                echo htmlspecialchars($row["title"]) . "</strong><br>";
                echo "<small>작성자: " . htmlspecialchars($row["author"]) . "</small><br>";
                echo htmlspecialchars(substr($row["content"], 0, 50)) . (strlen($row["content"]) > 50 ? "..." : "");
            } else {
                echo $row["title"] . "</strong><br>";
                echo "<small>작성자: " . $row["author"] . "</small><br>";
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

    <!-- 검색 기능 -->
    <div style="margin: 30px 0; padding: 20px; border: 2px solid #007cba; border-radius: 10px; background-color: #f0f8ff;">
        <h3 style="margin-top: 0; color: #007cba; text-align: center;">🔍 게시글 검색</h3>
        
        <?php if (!empty($search_query)): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
            <?php if ($xss2_protection): ?>
                "<strong><?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?></strong>" 검색 결과: <?php echo $total_posts; ?>개 게시글
            <?php else: ?>
                "<strong><?php echo $search_query; ?></strong>" 검색 결과: <?php echo $total_posts; ?>개 게시글 <!-- Reflected XSS 취약점 -->
            <?php endif; ?>
            <a href="list.php" style="margin-left: 10px; color: #155724; text-decoration: underline;">전체 목록 보기</a>
        </div>
        <?php endif; ?>
        
        <form method="GET" style="text-align: center;">
            <div style="display: inline-block; margin-right: 10px;">
                <input type="text" 
                       name="search" 
                       value="<?php echo $xss2_protection ? htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') : $search_query; ?>" 
                       placeholder="제목, 내용, 작성자로 검색..." 
                       style="padding: 10px; border: 2px solid #ccc; border-radius: 5px; width: 300px; font-size: 14px;">
            </div>
            <button type="submit" 
                    style="padding: 10px 20px; background-color: #007cba; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: bold;">
                검색
            </button>
            <?php if (!empty($search_query)): ?>
            <a href="list.php" 
               style="display: inline-block; margin-left: 10px; padding: 10px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 5px; font-size: 14px;">
                초기화
            </a>
            <?php endif; ?>
        </form>
        
        <div style="margin-top: 15px; font-size: 12px; color: #666; text-align: center;">
            💡 팁: 제목, 내용, 작성자 모든 항목에서 검색됩니다
        </div>
        
        <!-- SQL Injection 취약점 힌트 (개발/테스트용) -->
        <div style="margin-top: 10px; font-size: 11px; color: #dc3545; text-align: center; font-style: italic;">
            ⚠️ 보안 테스트: 이 검색 기능은 의도적으로 SQL Injection에 취약하게 구현되었습니다
        </div>
    </div>

</body>
</html>