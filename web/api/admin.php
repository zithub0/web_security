<?php
include_once('../includes/db.php');
header("Content-Type: application/json");

$response = array();

// Basic authentication (for demonstration purposes)
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    $response['status'] = 'error';
    $response['message'] = 'Authentication required';
    echo json_encode($response);
    exit;
}

$username = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];

// In a real application, you would validate the username and password against a database.
if ($username !== 'admin' || $password !== 'admin') {
    $response['status'] = 'error';
    $response['message'] = 'Invalid credentials';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 공지사항 작성
    $data = json_decode(file_get_contents("php://input"));
    $title = $data->title;
    $content = $data->content;

    $sql = "INSERT INTO notices (title, content) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $title, $content);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Notice created successfully';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error creating notice';
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    // 공지사항 수정
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id;
    $title = $data->title;
    $content = $data->content;

    $sql = "UPDATE notices SET title = ?, content = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $title, $content, $id);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Notice updated successfully';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error updating notice';
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // 공지사항 삭제
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id;

    $sql = "DELETE FROM notices WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Notice deleted successfully';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error deleting notice';
    }
}

echo json_encode($response);
?>