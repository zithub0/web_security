# 프로젝트 진행 및 변경사항 요약

이 문서는 초기 `Readme.md`의 요구사항을 기반으로 실제 구현된 내용과 변경사항을 정리합니다.

---

## 1. 초기 요구사항 (from `Readme.md`)

- **프로젝트 목표:** OWASP Top 10 취약점 테스트용 해킹 실습 서버 구축
- **기술 스택:** PHP, Apache, MySQL
- **환경:** Docker를 이용한 컨테이너화
- **주요 기능:**
    - 기본 웹 애플리케이션 (메인, 로그인, 회원가입, 게시판, 파일 업로드)
    - RESTful API (인증, 게시판, 공지사항, 관리자)
- **데이터 관리:** Docker Volume을 통한 데이터 영속성 확보

---

## 2. 구현된 내용

상기 요구사항에 따라 다음과 같이 시스템을 구현했습니다.

### 가. 서버 환경 구축

- **`docker-compose.yml`**: `web` (PHP/Apache) 서비스와 `db` (MySQL) 서비스를 정의하고, 두 컨테이너를 연결했습니다.
    - `web` 서비스는 포트 `80`을 호스트와 연결합니다.
    - `web` 서비스의 `/var/www/html` 디렉토리는 로컬의 `./web` 폴더와 마운트됩니다.
    - `db` 서비스의 데이터는 `db_data`라는 Docker Volume을 통해 영속적으로 저장됩니다.
- **`Dockerfile`**: `php:7.4-apache` 이미지를 기반으로 `mysqli`, `pdo`, `pdo_mysql` PHP 확장을 설치하여 데이터베이스와 연동할 수 있도록 커스텀 이미지를 빌드했습니다.

### 나. 데이터베이스 스키마 생성

`docker exec` 명령어를 통해 실행 중인 `db` 컨테이너에 접속하여 다음과 같은 테이블을 생성했습니다.

- **`users`**: 사용자 정보 (id, username, password) 저장
- **`board`**: 게시판 게시글 (id, username, content) 저장
- **`notices`**: 공지사항 (id, title, content, created_at) 저장

### 다. 웹 애플리케이션 및 API 구현

`Readme.md`에 명시된 모든 PHP 파일과 API 엔드포인트를 `./web` 디렉토리 하위에 구현했습니다.

- **공통 기능:**
    - `includes/db.php`: 데이터베이스 연결을 위한 공통 코드
- **웹 페이지:**
    - `index.php`: 메인 페이지
    - `login.php` / `register.php`: 사용자 인증 기능
    - `board.php`: XSS, SQL Injection 취약점을 포함한 게시판
    - `upload.php`: 파일 업로드 취약점을 포함한 파일 업로드 기능
- **API 엔드포인트:**
    - `api/auth.php`: JWT 토큰 기반 인증 API (현재는 임시 토큰 발급)
    - `api/board.php`: 게시판 CRUD API
    - `api/notice.php`: 공지사항 조회 API
    - `api/admin.php`: 공지사항 관리 API (생성, 수정, 삭제)

---

## 3. 변경 및 확정된 사항

- **관리자 계정 정보:**
    - 관리자 API (`/api/admin.php`)에 접근하기 위한 기본 인증 정보가 다음과 같이 확정되었습니다.
        - **ID:** `admin`
        - **Password:** `admin`
- **일반 사용자 계정:**
    - 웹 애플리케이션의 경우, 별도의 기본 계정은 제공되지 않습니다. 사용자가 직접 **`/register.php`** 페이지를 통해 계정을 생성한 후 사용해야 합니다.
- **JWT 라이브러리:**
    - `api/auth.php`의 JWT(JSON Web Token) 기능은 현재 실제 라이브러리 연동 없이 임시 더미 토큰(`dummy_jwt_token`)을 반환하도록 구현되어 있습니다. 추후 `php-jwt`와 같은 라이브러리를 연동하여 실제 토큰을 생성하고 검증하는 로직을 추가해야 합니다.
- **보안 취약점:**
    - `board.php`와 `upload.php`는 `Readme.md`의 요구사항에 따라 의도적으로 SQL Injection, XSS, Path Traversal 등의 보안 취약점을 포함하여 구현되었습니다.

---

## 4. 실행 방법

1.  Docker Desktop 실행
2.  터미널에서 `docker-compose up -d` 명령어 실행
3.  웹 브라우저에서 `http://localhost` 로 접속
