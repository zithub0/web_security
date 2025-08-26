<?php
include_once('includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    // 비밀번호 일치 확인
    if ($password !== $password_confirm) {
        $error_message = "비밀번호가 일치하지 않습니다.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 신규 회원가입은 기본적으로 일반 사용자(user) 권한
        $sql = "INSERT INTO users (username, password, role_id) VALUES (?, ?, 2)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("ss", $username, $hashed_password);
            
            if ($stmt->execute()) {
                $success_message = "회원가입이 완료되었습니다! 일반 사용자 권한으로 등록되었습니다.";
            } else {
                $error_message = "오류: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "쿼리 준비 오류: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 - 커뮤니티 웹사이트</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h2 {
            color: #333;
            font-size: 1.8em;
            margin-bottom: 10px;
        }

        .register-header p {
            color: #666;
            font-size: 0.9em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #28a745;
        }

        .register-btn {
            width: 100%;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .register-btn:hover {
            transform: translateY(-2px);
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error-message {
            background: #ffe6e6;
            color: #d63384;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #007cba;
            text-decoration: none;
            margin: 0 10px;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.9em;
        }

        .info-box h4 {
            color: #333;
            margin-bottom: 10px;
        }

        .info-box ul {
            margin-left: 20px;
            color: #666;
        }

        .info-box li {
            margin: 3px 0;
        }

        .password-match-message {
            margin-top: 5px;
            font-size: 0.9em;
            font-weight: 500;
        }

        .password-match {
            color: #28a745;
        }

        .password-no-match {
            color: #dc3545;
        }

        .form-group input.valid {
            border-color: #28a745;
        }

        .form-group input.invalid {
            border-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h2>👤 회원가입</h2>
            <p>새 계정을 만들어 커뮤니티에 참여하세요</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
                <br><br>
                <a href="login.php" style="color: #155724; font-weight: bold;">로그인 페이지로 이동 →</a>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($success_message)): ?>
        <form method="post" id="registerForm">
            <div class="form-group">
                <label for="username">사용자명</label>
                <input type="text" name="username" id="username" required minlength="3" maxlength="50" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">비밀번호</label>
                <input type="password" name="password" id="password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="password_confirm">비밀번호 확인</label>
                <input type="password" name="password_confirm" id="password_confirm" required minlength="6">
                <div id="password-match-message" class="password-match-message"></div>
            </div>
            <button type="submit" class="register-btn" id="submitBtn">회원가입</button>
        </form>
        <?php endif; ?>

        <div class="links">
            <a href="login.php">이미 계정이 있으신가요?</a> |
            <a href="index.php">메인으로</a>
        </div>

        <div class="info-box">
            <h4>📋 회원가입 안내</h4>
            <ul>
                <li>사용자명: 3-50자 (영문, 숫자, 한글 가능)</li>
                <li>비밀번호: 최소 6자 이상</li>
                <li>가입 시 일반 사용자 권한으로 등록됩니다</li>
                <li>모든 기본 기능을 사용할 수 있습니다</li>
            </ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirm');
            const matchMessage = document.getElementById('password-match-message');
            const submitBtn = document.getElementById('submitBtn');
            
            function checkPasswordMatch() {
                if (password.value === '' && passwordConfirm.value === '') {
                    matchMessage.textContent = '';
                    passwordConfirm.classList.remove('valid', 'invalid');
                    return;
                }
                
                if (password.value === passwordConfirm.value) {
                    matchMessage.textContent = '✅ 비밀번호가 일치합니다';
                    matchMessage.className = 'password-match-message password-match';
                    passwordConfirm.classList.add('valid');
                    passwordConfirm.classList.remove('invalid');
                } else {
                    matchMessage.textContent = '❌ 비밀번호가 일치하지 않습니다';
                    matchMessage.className = 'password-match-message password-no-match';
                    passwordConfirm.classList.add('invalid');
                    passwordConfirm.classList.remove('valid');
                }
            }
            
            // 실시간 검증
            password.addEventListener('input', checkPasswordMatch);
            passwordConfirm.addEventListener('input', checkPasswordMatch);
            
            // 폼 제출 시 최종 검증
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                if (password.value !== passwordConfirm.value) {
                    e.preventDefault();
                    alert('비밀번호가 일치하지 않습니다. 다시 확인해주세요.');
                    passwordConfirm.focus();
                }
            });
        });
    </script>
</body>
</html>