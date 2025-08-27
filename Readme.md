## ✅ 프로젝트 요약	
- OWASP Top 10 취약점을 테스트할 수 있는 해킹 실습 서버를 구축
- 기본적인 PHP 기반 웹 애플리케이션 + API 기반의 기능 제공
- PHP + Apache 컨테이너에서 웹 애플리케이션 실행, 데이터는 Docker Volume을 통해 영구 저장
- 코드 변경 시 web/ 폴더에 파일 추가

---
## 프로젝트 구조

```
web_security/
├── docker-compose.yml          # Docker 컨테이너 오케스트레이션 설정
├── config/                     # 컨테이너 설정 파일들
│   ├── Dockerfile             # 커스텀 PHP/Apache 이미지 빌드 설정
│   ├── custom-apache.conf     # Apache 웹서버 설정
│   ├── php.ini                # PHP 설정 (RFI 테스트용 allow_url_include 활성화)
│   └── init.sql               # MySQL 초기 데이터베이스 스키마 및 데이터
├── history/                    # 작업 이력 문서
└── web/                        # 웹 애플리케이션 소스 코드
    ├── index.php               # 메인 대시보드 페이지
    ├── login.php               # 로그인 페이지
    ├── logout.php              # 로그아웃 처리
    ├── register.php            # 회원가입 페이지
    ├── security.php            # 🔒 보안 설정 제어판
    ├── upload.php              # 파일 업로드 페이지
    ├── file_viewer.php         # 파일 뷰어 (LFI/RFI 취약점)
    ├── includes/               # 공통 라이브러리
    │   ├── auth.php           # 인증 관련 함수
    │   └── db.php             # 데이터베이스 연결 설정
    ├── board/                  # 게시판 관련 페이지
    │   ├── list.php           # 게시글 목록
    │   ├── view.php           # 게시글 상세보기
    │   └── write.php          # 게시글 작성
    ├── api/                    # REST API 엔드포인트
    │   ├── auth.php           # 인증 API
    │   ├── board.php          # 게시판 API
    │   ├── notice.php         # 공지사항 API
    │   └── admin.php          # 관리자 API
    └── uploads/                # 업로드된 파일 저장소
        ├── a.jpg              # 테스트 이미지
        ├── phpinfo.php        # PHP 정보 확인용
        ├── shell.php          # 테스트용 웹셸 (⚠️ 보안 위험)
        └── shell.phtml        # 웹셸 복사본
```


---


## ✅ 주요 파일 설명

### 🔒 보안 관련 파일
- **`security.php`**: **중앙 보안 제어판** - 모든 보안 기능을 토글로 제어
  - XSS, CSRF, SQL Injection, LFI, RFI 대책 설정
  - 실시간 보안 상태 확인 및 제어
- **`file_viewer.php`**: **파일 뷰어** (LFI/RFI 취약점 실습)
  - 디렉토리 트레버셜 공격 테스트
  - 원격 파일 포함 공격 테스트
  - 보안 설정에 따른 동적 차단

### 🌐 웹 페이지
- **`index.php`**: 메인 대시보드 - 프로젝트 개요 및 취약점 목록
- **`login.php`**: 로그인 페이지 (권한별 계정 테스트)
- **`register.php`**: 회원가입 페이지
- **`upload.php`**: 파일 업로드 기능 (악성 파일 업로드 실습)

### 📝 게시판 시스템
- **`board/list.php`**: 게시글 목록 (SQL Injection, XSS 실습)
- **`board/view.php`**: 게시글 상세보기 (XSS, CSRF 실습)
- **`board/write.php`**: 게시글 작성 (CSRF 실습)

### 🔌 API 엔드포인트
- **`api/auth.php`**: 인증 API (JWT 토큰 관리)
- **`api/board.php`**: 게시판 CRUD API
- **`api/notice.php`**: 공지사항 조회 API
- **`api/admin.php`**: 관리자 전용 API

### 🛠️ 시스템 파일
- **`includes/auth.php`**: 인증 관련 함수 및 권한 체크
- **`includes/db.php`**: 데이터베이스 연결 설정
- **`uploads/`**: 업로드된 파일 저장소
  - `shell.php`, `shell.phtml`: 웹셸 테스트 파일 (⚠️ 교육용)
  - `phpinfo.php`: PHP 설정 확인용

### 🔧 설정 파일
- **`docker-compose.yml`**: Docker 서비스 오케스트레이션 정의 (프로젝트 루트)
- **`config/Dockerfile`**: 커스텀 PHP/Apache 이미지 빌드 설정
- **`config/custom-apache.conf`**: Apache 웹서버 가상 호스트 설정
- **`config/php.ini`**: PHP 런타임 설정 (RFI 테스트용 설정 포함)
- **`config/init.sql`**: MySQL 초기 데이터베이스 스키마 및 계정 생성

---

## 🚀 시작하기

1. **Docker를 실행합니다.**

2. **프로젝트 루트 디렉토리에서 다음 명령어를 실행하여 Docker 컨테이너를 빌드하고 시작합니다.**
   ```bash
   docker-compose up -d --build
   ```

3. **웹 브라우저에서 `http://localhost:8080`로 접속하여 애플리케이션을 확인합니다.**

## 🔑 권한별 기본 계정 정보

권한 시스템이 적용된 기본 계정들이 자동으로 생성됩니다:

| 권한 | 사용자명 | 비밀번호 | 설명 |
|------|----------|----------|------|
| 👑 **관리자** | `admin` | `admin123` | 모든 기능 접근 가능, 관리자 전용 콘텐츠 접근 |
| 👤 **일반사용자** | `user` | `user123` | 기본 기능 사용, 게시글 작성/수정 가능 |
| 👻 **게스트** | `guest` | `guest123` | 읽기 전용 접근, 제한된 기능만 사용 가능 |

### 권한별 기능

#### 👑 관리자 (Admin)
- 모든 게시글 읽기/쓰기/수정/삭제
- 관리자 전용 콘텐츠 접근
- 사용자 관리
- 시스템 설정 변경
- 파일 업로드/다운로드

#### 👤 일반사용자 (User)
- 일반 게시글 읽기/쓰기
- 자신의 게시글 수정/삭제
- 파일 업로드 (제한적)
- 댓글 작성

#### 👻 게스트 (Guest)
- 일반 게시글 읽기 전용
- 파일 다운로드 (제한적)
- 회원가입 가능

---

## ✅ 게시판 API 설계 (예시)

| 메서드 | 엔드포인트       | 설명 |
|--------|-----------------|------|
| GET    | `/api/board`  | 게시글 목록 조회 |
| POST   | `/api/board` | 새 게시글 작성 |

---