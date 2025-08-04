
## ✅ 프로젝트 요약	
- OWASP Top 10 취약점을 테스트할 수 있는 해킹 실습 서버를 구축
- 기본적인 PHP 기반 웹 애플리케이션 + API 기반의 기능 제공
- PHP + Apache 컨테이너에서 웹 애플리케이션 실행, 데이터는 Docker Volume을 통해 영구 저장
- 코드 변경 시 web/ 폴더에 파일 추가

---
## 프로젝트 구조

* **`docker-compose.yml`**: 이 파일은 여러 개의 Docker 컨테이너(PHP/Apache 및 MySQL)로 구성된 애플리케이션을 정의하고 오케스트레이션합니다.
* **`php-apache/`**:
    * **`Dockerfile`**: 커스텀 PHP 및 Apache Docker 이미지를 빌드하는 방법을 정의합니다. 필요한 PHP 확장이나 Apache 설정이 포함됩니다.
    * **`web/`**: 이 디렉토리에 PHP 웹 애플리케이션 코드가 위치합니다. `index.php` 파일 및 각종 API 엔드포인트 등을 이곳에 작성합니다.
* **`mysql/`**: MySQL 서비스를 나타내는 디렉토리입니다.
    * *(데이터는 Docker Volume에 저장)*: MySQL 데이터는 Docker 볼륨을 사용하여 컨테이너 외부에서 영속적으로 저장됩니다. 이는 MySQL 컨테이너가 제거되거나 업데이트되더라도 데이터가 안전하게 유지됨을 의미합니다.


---


## ✅ 파일 설명
- **index.php**: 메인 페이지
- **login.php / register.php**: 인증 기능 구현
- **board.php**: 일반 게시판 (XSS, CSRF, SQL Injection 실습 가능)
- **upload.php**: 파일 업로드 기능 (RCE, Path Traversal 실습)
- **api/**:
  - `auth.php`: JWT 로그인/회원가입 API
  - `board.php`: 게시판 CRUD API
  - `notice.php`: 공지사항 조회 API (리스트, 상세 보기)
  - `admin.php`: 관리자 API (공지 작성, 수정, 삭제)
- **includes/**: 공통 코드 (DB 연결, 함수, 세션 관리)
- **uploads/**: 업로드된 파일 저장 (취약한 접근 제어 테스트 가능)
- **config/**: 환경설정 파일 (.env를 통한 DB 인증 정보 노출 가능)
- **logs/**: 접근/에러 로그 (로그 미비 취약점 실습)

---

## ✅ 공지사항 API 설계 (예시)

| 메서드 | 엔드포인트       | 설명 |
|--------|-----------------|------|
| GET    | `/api/notice`  | 공지사항 목록 조회 |
| GET    | `/api/notice/{id}` | 공지사항 상세 보기 |
| POST   | `/api/admin/notice` | 관리자 공지 작성 |
| PUT    | `/api/admin/notice/{id}` | 공지 수정 |
| DELETE | `/api/admin/notice/{id}` | 공지 삭제 |

---