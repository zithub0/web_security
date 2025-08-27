# 웹 보안 실습 플랫폼 개발 TODO

## 🔐 웹 취약점 추가 개발

### ✅ 완료된 항목
- [x] **CSRF (Cross-Site Request Forgery)** - 토큰 기반 보호 구현
- [x] **XSS (Cross-Site Scripting)** - Stored & Reflected XSS 구현
- [x] **SQL Injection** - Boolean, Time-based Blind SQLi 구현
- [x] **LFI/RFI (File Inclusion)** - 로컬/원격 파일 포함 공격 구현
- [x] **File Upload Vulnerabilities** - 웹셸 업로드 취약점 구현

### 🚀 추가 개발 필요 항목

#### 📊 SQL Injection 고도화
- [ ] **Union-based SQL Injection** - 데이터 추출 공격 구현
- [ ] **Error-based SQL Injection** - 에러 메시지 기반 공격
- [ ] **Second-order SQL Injection** - 간접 SQL 인젝션
- [ ] **NoSQL Injection** - MongoDB 등 NoSQL 데이터베이스 공격
- [ ] **검색 로직 최적화** - Sleep 함수 중복 실행 문제 해결

#### 🌐 웹 취약점
- [ ] **SSRF (Server-Side Request Forgery)** - 서버 측 요청 위조
- [ ] **SSTI (Server-Side Template Injection)** - 서버 템플릿 인젝션
- [ ] **XXE (XML External Entity)** - XML 외부 엔티티 공격
- [ ] **역직렬화 취약점** - Insecure Deserialization
- [ ] **JWT 취약점** - JSON Web Token 보안 이슈
- [ ] **Command Injection** - 명령어 주입 공격
- [ ] **Path Traversal** - 디렉토리 순회 공격


## 🔐 기본 보안 메커니즘 추가 개발

#### 🔒 기본 보안 메커니즘
- [ ] **SameSite 쿠키** - CSRF 추가 보호 계층
- [ ] **HSTS (HTTP Strict Transport Security)** - 전송 보안 강화
- [ ] **CSP (Content Security Policy)** - XSS 추가 방어
- [ ] **CORS (Cross-Origin Resource Sharing)** - 교차 출처 리소스 공유 설정

## 🎨 UI/UX 개선

### 📱 인터페이스 개선
- [ ] **네비게이션 바 재구성** - 직관적인 메뉴 구조
- [ ] **실시간 알림** - 공격 성공/실패 피드백

### 👥 사용자 관리 고도화
- [ ] **관리자 권한 부여 시스템** - 세분화된 권한 관리
- [ ] **사용자 역할 관리** - Admin, Moderator, User 구분
- [ ] **프로필 관리** - 개인 설정 및 통계
- [ ] **로그인 이력** - 접속 기록 및 보안 로그

### ✏️ 게시판 기능 확장
- [ ] **게시글 수정/삭제** - 작성자 권한 확인
- [ ] **댓글 수정/삭제** - 댓글 관리 기능

---


**마지막 업데이트**: 2024-08-27
