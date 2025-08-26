<?php
session_start();

// 보안 설정 처리
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_security':
                $_SESSION['security_settings'] = [
                    'xss1_protection' => isset($_POST['xss1']) ? true : false,
                    'xss2_protection' => isset($_POST['xss2']) ? true : false,
                    'csrf1_protection' => isset($_POST['csrf1']) ? true : false,
                    'csrf2_protection' => isset($_POST['csrf2']) ? true : false,
                    'sql_protection' => isset($_POST['sql']) ? true : false,
                    'search_sql_protection' => isset($_POST['search_sql']) ? true : false,
                    'file_upload_protection' => isset($_POST['file_upload']) ? true : false
                ];
                $message = "보안 설정이 저장되었습니다.";
                break;
                
            case 'security_all_on':
                $_SESSION['security_settings'] = [
                    'xss1_protection' => true,
                    'xss2_protection' => true,
                    'csrf1_protection' => true,
                    'csrf2_protection' => true,
                    'sql_protection' => true,
                    'search_sql_protection' => true,
                    'file_upload_protection' => true
                ];
                $message = "모든 보안 기능이 활성화되었습니다.";
                break;
                
            case 'security_all_off':
                $_SESSION['security_settings'] = [
                    'xss1_protection' => false,
                    'xss2_protection' => false,
                    'csrf1_protection' => false,
                    'csrf2_protection' => false,
                    'sql_protection' => false,
                    'search_sql_protection' => false,
                    'file_upload_protection' => false
                ];
                $message = "모든 보안 기능이 비활성화되었습니다. ⚠️ 위험한 상태입니다!";
                break;
                
            case 'security_recommended':
                $_SESSION['security_settings'] = [
                    'xss1_protection' => true,
                    'xss2_protection' => true,
                    'csrf1_protection' => true,
                    'csrf2_protection' => true,
                    'sql_protection' => true,
                    'search_sql_protection' => true,
                    'file_upload_protection' => true
                ];
                $message = "권장 보안 설정이 적용되었습니다.";
                break;
        }
    }
}

// 기본 보안 설정값
if (!isset($_SESSION['security_settings'])) {
    $_SESSION['security_settings'] = [
        'xss1_protection' => false,
        'xss2_protection' => false,
        'csrf1_protection' => false,
        'csrf2_protection' => false,
        'sql_protection' => false,
        'search_sql_protection' => false,
        'file_upload_protection' => false
    ];
}

$settings = $_SESSION['security_settings'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>보안 설정 제어판</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .security-panel {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .panel-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .panel-title {
            font-size: 28px;
            color: #007cba;
            margin-bottom: 10px;
        }
        
        .panel-subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .quick-btn {
            flex: 1;
            min-width: 150px;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .quick-btn.safe {
            background-color: #28a745;
            color: white;
        }
        
        .quick-btn.danger {
            background-color: #dc3545;
            color: white;
        }
        
        .quick-btn.recommended {
            background-color: #007cba;
            color: white;
        }
        
        .quick-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .security-section {
            margin-bottom: 25px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #fafafa;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #007cba;
            padding-bottom: 5px;
        }
        
        .security-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .security-item:last-child {
            border-bottom: none;
        }
        
        .security-info {
            flex: 1;
        }
        
        .security-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .security-desc {
            font-size: 13px;
            color: #666;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #2196F3;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .status-indicator {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 15px;
        }
        
        .status-on {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-off {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .navigation {
            text-align: center;
            margin-top: 30px;
        }
        
        .nav-link {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            background-color: #007cba;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s;
        }
        
        .nav-link:hover {
            background-color: #0056b3;
        }
        
        .save-btn {
            width: 100%;
            padding: 15px;
            background-color: #007cba;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .save-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="security-panel">
        <div class="panel-header">
            <div class="panel-title">🔒 보안 설정 제어판</div>
            <div class="panel-subtitle">웹 애플리케이션의 보안 기능을 중앙에서 관리합니다</div>
        </div>

        <?php if (isset($message)): ?>
        <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- 빠른 설정 버튼들 -->
        <div class="quick-actions">
            <form method="post" style="flex: 1;">
                <input type="hidden" name="action" value="security_all_on">
                <button type="submit" class="quick-btn safe">🛡️ 전체 보안 ON</button>
            </form>
            <form method="post" style="flex: 1;">
                <input type="hidden" name="action" value="security_recommended">
                <button type="submit" class="quick-btn recommended">⭐ 권장 설정</button>
            </form>
            <form method="post" style="flex: 1;">
                <input type="hidden" name="action" value="security_all_off">
                <button type="submit" class="quick-btn danger">⚠️ 전체 보안 OFF</button>
            </form>
        </div>

        <!-- 세부 보안 설정 -->
        <form method="post">
            <input type="hidden" name="action" value="update_security">

            <!-- 게시판 페이지 설정 박스 -->
            <div style="border: 3px solid #007cba; border-radius: 10px; padding: 20px; margin-bottom: 30px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <div style="text-align: center; font-size: 20px; font-weight: bold; color: #007cba; margin-bottom: 15px; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                    📝 게시판 뷰 페이지 설정
                </div>
                <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 25px;">
                    게시판 관련 페이지(목록, 상세보기, 댓글)에서 사용되는 보안 기능들을 설정합니다.
                </p>

                <!-- XSS 대책 -->
                <div class="security-section">
                    <div class="section-title">🚫 XSS (Cross-Site Scripting) 대책</div>
                    
                    <div class="security-item">
                        <div class="security-info">
                            <div class="security-label">XSS 대책1 - 응답 인코딩</div>
                            <div class="security-desc">HTTP 응답 헤더에 문자 인코딩을 지정하여 XSS 공격을 방지</div>
                        </div>
                        <div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="xss1" <?php echo $settings['xss1_protection'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span class="status-indicator <?php echo $settings['xss1_protection'] ? 'status-on' : 'status-off'; ?>">
                                <?php echo $settings['xss1_protection'] ? 'ON' : 'OFF'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="security-item">
                        <div class="security-info">
                            <div class="security-label">XSS 대책2 - 출력 필터링</div>
                            <div class="security-desc">htmlspecialchars()를 사용하여 HTML 특수문자를 안전하게 처리</div>
                        </div>
                        <div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="xss2" <?php echo $settings['xss2_protection'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span class="status-indicator <?php echo $settings['xss2_protection'] ? 'status-on' : 'status-off'; ?>">
                                <?php echo $settings['xss2_protection'] ? 'ON' : 'OFF'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- CSRF 대책 -->
                <div class="security-section">
                    <div class="section-title">🔐 CSRF (Cross-Site Request Forgery) 대책</div>
                    
                    <div class="security-item">
                        <div class="security-info">
                            <div class="security-label">CSRF 대책1 - 토큰 생성</div>
                            <div class="security-desc">폼에 CSRF 토큰을 생성하여 삽입</div>
                        </div>
                        <div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="csrf1" <?php echo $settings['csrf1_protection'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span class="status-indicator <?php echo $settings['csrf1_protection'] ? 'status-on' : 'status-off'; ?>">
                                <?php echo $settings['csrf1_protection'] ? 'ON' : 'OFF'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="security-item">
                        <div class="security-info">
                            <div class="security-label">CSRF 대책2 - 토큰 검증</div>
                            <div class="security-desc">POST 요청 시 CSRF 토큰을 검증하여 위조 요청 차단</div>
                        </div>
                        <div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="csrf2" <?php echo $settings['csrf2_protection'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span class="status-indicator <?php echo $settings['csrf2_protection'] ? 'status-on' : 'status-off'; ?>">
                                <?php echo $settings['csrf2_protection'] ? 'ON' : 'OFF'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- SQL Injection 대책 -->
                <div class="security-section">
                    <div class="section-title">💉 SQL Injection 대책</div>
                    
                    <div class="security-item">
                        <div class="security-info">
                            <div class="security-label">SQL 인젝션 방지</div>
                            <div class="security-desc">Prepared Statements를 사용하여 게시글 상세보기 SQL 인젝션 공격 방지</div>
                        </div>
                        <div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="sql" <?php echo $settings['sql_protection'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span class="status-indicator <?php echo $settings['sql_protection'] ? 'status-on' : 'status-off'; ?>">
                                <?php echo $settings['sql_protection'] ? 'ON' : 'OFF'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 게시판 리스트 페이지 설정 박스 -->
            <div style="border: 3px solid #28a745; border-radius: 10px; padding: 20px; margin-bottom: 30px; background: linear-gradient(135deg, #f8f9fa 0%, #e8f5e8 100%);">
                <div style="text-align: center; font-size: 20px; font-weight: bold; color: #28a745; margin-bottom: 15px; border-bottom: 2px solid #28a745; padding-bottom: 10px;">
                    📋 게시판 리스트 페이지 설정
                </div>
                <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 25px;">
                    게시판 목록 페이지(검색, 페이징)에서 사용되는 보안 기능들을 설정합니다.
                </p>

                <!-- SQL Injection 대책 (검색기능) -->
                <div class="security-section">
                    <div class="section-title">💉 SQL Injection 대책 (검색기능)</div>
                    
                    <div class="security-item">
                        <div class="security-info">
                            <div class="security-label">SQL 인젝션 방지 (검색기능)</div>
                            <div class="security-desc">Prepared Statements를 사용하여 게시글 검색 SQL 인젝션 공격 방지</div>
                        </div>
                        <div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="search_sql" <?php echo $settings['search_sql_protection'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span class="status-indicator <?php echo $settings['search_sql_protection'] ? 'status-on' : 'status-off'; ?>">
                                <?php echo $settings['search_sql_protection'] ? 'ON' : 'OFF'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 파일 업로드 페이지 설정 박스 -->
            <div style="border: 3px solid #dc3545; border-radius: 10px; padding: 20px; margin-bottom: 30px; background: linear-gradient(135deg, #f8f9fa 0%, #ffe8e8 100%);">
                <div style="text-align: center; font-size: 20px; font-weight: bold; color: #dc3545; margin-bottom: 15px; border-bottom: 2px solid #dc3545; padding-bottom: 10px;">
                    📤 파일 업로드 페이지 설정
                </div>
                <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 25px;">
                    파일 업로드 페이지에서 사용되는 보안 기능들을 설정합니다.
                </p>

                <!-- 파일 업로드 보안 대책 -->
                <div class="security-section">
                    <div class="section-title">📁 파일 업로드 보안 대책</div>
                    
                    <div class="security-item">
                        <div class="security-info">
                            <div class="security-label">파일 타입 검증 (MIME)</div>
                            <div class="security-desc">업로드되는 파일의 확장자와 MIME 타입을 검사하여 악성 파일 업로드 방지</div>
                        </div>
                        <div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="file_upload" <?php echo $settings['file_upload_protection'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span class="status-indicator <?php echo $settings['file_upload_protection'] ? 'status-on' : 'status-off'; ?>">
                                <?php echo $settings['file_upload_protection'] ? 'ON' : 'OFF'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="save-btn">💾 설정 저장</button>
        </form>

        <div class="navigation">
            <a href="index.php" class="nav-link">🏠 홈으로</a>
            <a href="board/list.php" class="nav-link">📝 게시판</a>
        </div>
    </div>
</body>
</html>