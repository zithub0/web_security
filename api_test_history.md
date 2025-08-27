# 웹 보안 실습 플랫폼 개발 히스토리

이 문서는 웹 보안 실습 플랫폼 개발 과정에서 수행된 작업들을 기록합니다.

## 1. Start the services

The services were started using `docker-compose`.

```bash
docker-compose up -d
```

## 2. Register a new user

A new user was registered using the `register` endpoint.

```bash
curl -X POST -H "Content-Type: application/json" -d "{\"action\": \"register\", \"username\": \"testuser\", \"password\": \"testpassword\"}" http://localhost/api/auth.php
```

**Response:**
```json
{"status":"success","message":"User registered successfully"}
```

## 3. Log in as the new user

The user was logged in using the `login` endpoint.

```bash
curl -X POST -H "Content-Type: application/json" -d "{\"action\": \"login\", \"username\": \"testuser\", \"password\": \"testpassword\"}" http://localhost/api/auth.php
```

**Response:**
```json
{"status":"success","message":"Login successful","token":"dummy_jwt_token"}
```

## 4. Create a new post

A new post was created using the `board` endpoint.

```bash
curl -X POST -H "Content-Type: application/json" -d "{\"username\": \"testuser\", \"content\": \"This is a test post.\"}" http://localhost/api/board.php
```

**Response:**
```json
{"status":"success","message":"Post created successfully"}
```

## 5. Get all posts

All posts were retrieved from the `board` endpoint.

```bash
curl http://localhost/api/board.php
```

**Response:**
```json
{"status":"success","data":[{"id":"1","username":"tester0","content":"1111"},{"id":"2","username":"test","content":"123123213"},{"id":"3","username":"testuser","content":"This is a test post."}]}
```

---

## 📅 2024-08-27 웹 보안 플랫폼 대폭 개선 작업

### 🏗️ 프로젝트 구조 재구성
1. **config/ 폴더 생성 및 파일 이동**
   - `init.sql`, `php.ini`, `Dockerfile`, `custom-apache.conf`를 config/ 폴더로 이동
   - `docker-compose.yml`과 `Dockerfile`의 경로 설정 업데이트

2. **Docker 설정 최적화**
   - Docker 컨테이너 재시작 및 빌드 확인
   - `docker-compose.yml`을 최종적으로 프로젝트 루트에 배치

### 🔐 CSRF 보안 기능 통합
1. **CSRF 보호 메커니즘 단일화**
   - 기존 `csrf1_protection`과 `csrf2_protection`을 `csrf_protection`으로 통합
   - `security.php`에서 "🔗 게시판 통합 페이지 설정" 섹션으로 이동

2. **CSRF 공격 데모 페이지 구현**
   - `/web/uploads/csrf_attack.html` 생성
   - 자동 실행 기능과 수동 클릭 기능 포함

### 🐛 보드 파일 버그 수정
1. **변수 정의 순서 문제 해결**
   - `write.php`, `view.php`, `list.php`에서 `$xss1_protection` 변수 정의 순서 수정
   - `session_start()` 후 변수 정의가 선행되도록 개선

2. **SQL 인젝션 문제 해결**
   - XSS 공격 시 작은따옴표(')로 인한 SQL 구문 오류 식별
   - Prepared Statements 사용 시 안전하게 처리됨을 확인

### 🚨 XSS 공격 기능 완전 구현
1. **Stored XSS 기존 기능 유지**
   - 게시글 및 댓글 작성에서 XSS 스크립트 실행 가능
   - `htmlspecialchars()` 보호 기능 토글 가능

2. **Reflected XSS 신규 추가**
   - `list.php`의 검색 기능에 Reflected XSS 취약점 구현
   - URL 파라미터를 통한 즉시 실행 가능
   - 다양한 페이로드 테스트 지원

### 📝 보안 설정 UI 개선
1. **게시판 통합 페이지 설정 섹션 신설**
   - CSRF 토큰 보호
   - XSS 대책1 (응답 인코딩)
   - XSS 대책2 (출력 필터링)

2. **LFI/RFI 섹션 분리**
   - "👁️ 파일 뷰어 페이지 설정"에서 LFI와 RFI를 별도 토글로 분리

### 📚 문서 업데이트
1. **Test.md 대폭 개선**
   - Reflected XSS 테스트 시나리오 추가
   - Stored XSS와 Reflected XSS 비교표 작성
   - 고급 XSS 페이로드 예시 추가
   - 실제 공격 시나리오 설명 포함

2. **README.md 프로젝트 구조 업데이트**
   - config/ 폴더 구조 반영
   - 주요 파일 설명 업데이트

### 🔧 기술적 개선사항
- **보안 설정 세션 관리**: `$_SESSION['security_settings']`로 통합 관리
- **에러 처리 개선**: SQL 오류 시 상세한 디버깅 정보 제공
- **코드 안전성**: 모든 사용자 입력에 대한 적절한 검증 및 인코딩 적용

### 🎯 테스트 시나리오 검증
1. **CSRF 공격 테스트**
   - 토큰 없이 공격 성공 확인
   - 토큰 보호 시 차단 확인

2. **XSS 공격 테스트**
   - Stored XSS: `<script>alert('저장된 XSS!');</script>`
   - Reflected XSS: URL을 통한 즉시 실행
   - 보호 기능 활성화 시 안전한 인코딩 확인

### ⚡ 성능 및 사용성 개선
- Docker 빌드 최적화
- 보안 설정 UI 색상 코딩 및 직관적 배치
- 실시간 보안 토글 기능으로 즉시 테스트 가능
