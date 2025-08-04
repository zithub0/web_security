<?php
include_once('../includes/db.php');
header("Content-Type: application/json");

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // 게시글 목록 조회
    $sql = "SELECT * FROM board";
    $result = $conn->query($sql);
    $posts = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }
    $response['status'] = 'success';
    $response['data'] = $posts;
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 게시글 작성
    $data = json_decode(file_get_contents("php://input"));
    $username = $data->username;
    $content = $data->content;

    $sql = "INSERT INTO board (username, content) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $content);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Post created successfully';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error creating post';
    }
}

echo json_encode($response);
?>