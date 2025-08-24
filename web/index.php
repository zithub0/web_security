<?php
session_start();
include_once('includes/db.php');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>웹 보안 실습 플랫폼</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            margin: 0;
        }

        .navbar {
            background: linear-gradient(135deg, #007cba 0%, #0056b3 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .nav-brand {
            color: white;
            font-size: 1.5em;
            font-weight: bold;
            text-decoration: none;
            padding: 15px 0;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 20px 15px;
            display: block;
            transition: background-color 0.3s ease;
        }

        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-status {
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            background-color: rgba(255,255,255,0.2);
            font-size: 0.9em;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.2em;
            color: #666;
            font-weight: 300;
        }

        .content {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
            
            .nav-user {
                flex-direction: column;
                align-items: flex-end;
                gap: 5px;
            }
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .footer p {
            color: #666;
            font-size: 0.9em;
        }

    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">커뮤니티 웹사이트</a>
            
            <ul class="nav-menu">
                <li class="nav-item"><a href="board/list.php" class="nav-link">📝 게시판</a></li>
                <li class="nav-item"><a href="file_viewer.php" class="nav-link">📁 파일 뷰어</a></li>
                <li class="nav-item"><a href="upload.php" class="nav-link">📤 파일 업로드</a></li>
            </ul>

            <div class="nav-user">
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <div class="user-status">
                        ✅ <?php echo htmlspecialchars($_SESSION['username'] ?? 'Unknown'); ?>님
                    </div>
                    <a href="logout.php" class="nav-link">🚪 로그아웃</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">🔑 로그인</a>
                    <a href="register.php" class="nav-link">👤 회원가입</a>
                <?php endif; ?>
                <a href="security.php" class="nav-link">⚙️ 사이트 설정</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="content">
            <div class="header">
                <h1>웹사이트 메인 페이지</h1>
                <p>다양한 기능을 제공하는 커뮤니티 웹사이트입니다</p>
            </div>

            <div class="footer">
                <p>
                    이 웹사이트는 사용자 간의 소통과 정보 공유를 위한 커뮤니티 플랫폼입니다.
                </p>
            </div>
        </div>
    </div>
</body>
</html>