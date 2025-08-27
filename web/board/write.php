<?php
include_once('../includes/db.php');
session_start();

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
$xss1_protection = $settings['xss1_protection'];
$xss2_protection = $settings['xss2_protection'];
$csrf_protection = $settings['csrf_protection'];
$sql_protection = $settings['sql_protection'];

//XSS 대책1 : 응답의 문자 인코딩 지정
if ($xss1_protection) {
    header("Content-Type: text/html; charset=UTF-8");
}

// 로그인 확인
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['content'])) {
    // CSRF 대책: POST 요청 시 토큰 검증
    if ($csrf_protection) {
        if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
            die('CSRF token validation failed.');
        }
    }

    $content = $_POST['content'];
    $title = isset($_POST['title']) ? $_POST['title'] : '제목 없음';
    $author = $_SESSION['username'];
    
    if ($sql_protection) {
        // SQL 인젝션 대책: Prepared Statements 사용
        $stmt = $conn->prepare("INSERT INTO posts (title, content, author) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $content, $author);
        $stmt->execute();
    } else {
        // SQL Injection 취약
        $sql = "INSERT INTO posts (title, content, author) VALUES ('$title', '$content', '$author')";
        $conn->query($sql);
    }
    
    header("Location: list.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>새 글 작성</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
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
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 200px;
        }
        
        .form-group textarea:focus {
            border-color: #007cba;
            outline: none;
        }
        
        .buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
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
        
        .user-info {
            background: #e3f2fd;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            color: #1976d2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>새 글 작성</h2>
        </div>
        
        <div class="user-info">
            📝 작성자: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        </div>
        
        <form method="post">
            <?php
            // CSRF 대책: 폼에 숨겨진 토큰 추가
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
            
            <div class="form-group">
                <label for="content">글 내용</label>
                <textarea name="content" id="content" placeholder="글 내용을 입력하세요..." required></textarea>
            </div>
            
            <div class="buttons">
                <a href="list.php" class="btn btn-secondary">취소</a>
                <button type="submit" class="btn btn-primary">글 작성</button>
            </div>
        </form>
    </div>
</body>
</html>