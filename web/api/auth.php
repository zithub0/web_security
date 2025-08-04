<?php
include_once('../includes/db.php');
header("Content-Type: application/json");

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->action)) {
        if ($data->action == 'register') {
            // 회원가입 로직
            $username = $data->username;
            $password = password_hash($data->password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $password);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'User registered successfully';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error registering user';
            }
        } elseif ($data->action == 'login') {
            // 로그인 로직 (JWT 생성 필요)
            $username = $data->username;
            $password = $data->password;

            $sql = "SELECT * FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    // JWT 생성 및 반환 (추후 구현)
                    $response['status'] = 'success';
                    $response['message'] = 'Login successful';
                    $response['token'] = 'dummy_jwt_token'; // 임시 토큰
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Invalid password';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'User not found';
            }
        }
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>