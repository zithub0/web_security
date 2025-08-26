<?php
// 권한 체크 및 인증 관리 파일

// 권한 상수 정의
define('ROLE_ADMIN', 1);
define('ROLE_USER', 2);
define('ROLE_GUEST', 3);

// 사용자 로그인 체크
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// 사용자 권한 가져오기
function getUserRole() {
    if (!isLoggedIn()) {
        return ROLE_GUEST; // 로그인하지 않은 경우 게스트
    }
    return $_SESSION['role_id'] ?? ROLE_GUEST;
}

// 권한명 가져오기
function getRoleName($role_id = null) {
    if ($role_id === null) {
        $role_id = getUserRole();
    }
    
    switch ($role_id) {
        case ROLE_ADMIN:
            return 'admin';
        case ROLE_USER:
            return 'user';
        case ROLE_GUEST:
            return 'guest';
        default:
            return 'guest';
    }
}

// 관리자 권한 체크
function isAdmin() {
    return getUserRole() === ROLE_ADMIN;
}

// 일반 사용자 이상 권한 체크
function isUser() {
    $role = getUserRole();
    return $role === ROLE_ADMIN || $role === ROLE_USER;
}

// 최소 권한 체크
function hasMinimumRole($required_role) {
    $current_role = getUserRole();
    return $current_role <= $required_role; // 숫자가 낮을수록 높은 권한
}

// 권한 부족 시 접근 거부 처리
function requireRole($required_role, $redirect_url = 'login.php') {
    if (!hasMinimumRole($required_role)) {
        $role_names = [
            ROLE_ADMIN => '관리자',
            ROLE_USER => '일반 사용자',
            ROLE_GUEST => '게스트'
        ];
        
        if (!isLoggedIn()) {
            header("Location: $redirect_url?error=login_required");
        } else {
            $required_name = $role_names[$required_role] ?? '알 수 없음';
            header("Location: index.php?error=insufficient_permissions&required=$required_name");
        }
        exit();
    }
}

// 관리자 전용 페이지 접근 체크
function requireAdmin($redirect_url = 'login.php') {
    requireRole(ROLE_ADMIN, $redirect_url);
}

// 로그인된 사용자 전용 접근 체크
function requireLogin($redirect_url = 'login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect_url?error=login_required");
        exit();
    }
}

// 권한 정보 표시용 함수
function getPermissionInfo() {
    $role = getUserRole();
    $permissions = [];
    
    switch ($role) {
        case ROLE_ADMIN:
            $permissions = [
                '모든 게시글 읽기/쓰기/수정/삭제',
                '관리자 전용 콘텐츠 접근',
                '사용자 관리',
                '시스템 설정 변경',
                '파일 업로드/다운로드'
            ];
            break;
        case ROLE_USER:
            $permissions = [
                '일반 게시글 읽기/쓰기',
                '자신의 게시글 수정/삭제',
                '파일 업로드 (제한적)',
                '댓글 작성'
            ];
            break;
        case ROLE_GUEST:
        default:
            $permissions = [
                '일반 게시글 읽기 전용',
                '파일 다운로드 (제한적)',
                '회원가입 가능'
            ];
            break;
    }
    
    return $permissions;
}
?>