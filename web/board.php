<?php
include_once('includes/db.php');
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['content'])) {
        $content = $_POST['content'];
        $username = $_SESSION['username'];
        $sql = "INSERT INTO board (username, content) VALUES ('$username', '$content')"; // SQL Injection
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
            echo "</div>";
        }
    }
    ?>
</body>
</html>