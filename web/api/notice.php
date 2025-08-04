<?php
include_once('../includes/db.php');
header("Content-Type: application/json");

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        // 공지사항 상세 보기
        $id = $_GET['id'];
        $sql = "SELECT * FROM notices WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $response['status'] = 'success';
            $response['data'] = $result->fetch_assoc();
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Notice not found';
        }
    } else {
        // 공지사항 목록 조회
        $sql = "SELECT * FROM notices";
        $result = $conn->query($sql);
        $notices = array();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $notices[] = $row;
            }
        }
        $response['status'] = 'success';
        $response['data'] = $notices;
    }
}

echo json_encode($response);
?>