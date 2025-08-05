<?php


//XSS 대책1 : 응답의 문자 인코딩 지정
//header("Content-Type: text/html; charset=UTF-8");



include_once('includes/db.php');
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['content'])) {

        // CSRF 대책 2: POST 요청 시 토큰 검증
        // if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        //     die('CSRF token validation failed.');
        // }

        $content = $_POST['content'];
        $username = $_SESSION['username'];
        $sql = "INSERT INTO board (username, content) VALUES ('$username', '$content')"; // SQL Injection
        
        // SQL 인젝션 대책: Prepared Statements 사용
        // $stmt = $conn->prepare("INSERT INTO board (username, content) VALUES (?, ?)");
        // $stmt->bind_param("ss", $username, $content);
        // $stmt->execute();

        $conn->query($sql);
    }
}

$sql = "SELECT * FROM board";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Board</title>
</head>
<body>
    <h2>Board</h2>
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
    <form method="post">
        <?php
        // CSRF 대책 1: 폼에 숨겨진 토큰 추가
        // if (empty($_SESSION['token'])) {
        //     $_SESSION['token'] = bin2hex(random_bytes(32));
        // }
        // $token = $_SESSION['token'];
        ?>
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <textarea name="content" rows="5" cols="40"></textarea><br>
        <input type="submit" value="Post">
    </form>
    <?php endif; ?>

    <hr>

    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<div>";
            echo "<strong>" . $row["username"] . "</strong>: ";
            echo $row["content"]; // XSS
            //XSS 대책2 : htmlspecialchars 적용
            //echo htmlspecialchars($row["content"], ENT_QUOTES, 'UTF-8');
            echo "</div>";
        }
    }
    ?>
</body>
</html>