-- 웹 보안 실습 플랫폼 초기 데이터베이스 설정 (권한 시스템)
-- Database: webapp

USE webapp;

-- =============================================================================
-- 데이터 초기화 (시작 시 기존 데이터 모두 삭제)
-- =============================================================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

-- 권한 테이블 생성
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255)
);

-- 사용자 테이블 생성 (권한 시스템 적용)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role_id INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- 게시글 테이블 생성 (권한별 접근 제어)
CREATE TABLE IF NOT EXISTS posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    author VARCHAR(50) NOT NULL,
    is_admin_only BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 댓글 테이블 생성
CREATE TABLE IF NOT EXISTS comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- 권한 데이터 삽입
INSERT IGNORE INTO roles (id, role_name, description) VALUES 
(1, 'admin', '관리자 - 모든 기능 접근 가능'),
(2, 'user', '일반 사용자 - 기본 기능 사용 가능'),
(3, 'guest', '게스트 - 읽기 전용 접근');

-- 권한별 기본 계정 생성
-- admin/admin123 (관리자 권한)
-- user/user123 (일반 사용자 권한)
-- guest/guest123 (게스트 권한)
INSERT IGNORE INTO users (username, password, role_id, email) VALUES 
('admin', '$2y$10$6zzEbg4l33nTAoc0XiP48egGxtd0OC94FWwk4AOtNQdM4k9GWz.Mq', 1, 'admin@example.com'),
('user', '$2y$10$PC7cNhOfl31Qh1dhn7uvnOZEWn0amy9.fmnv36MOgIn/SqBelOuay', 2, 'user@example.com'),
('guest', '$2y$10$vbBoRdbXCZZ1Ifjl5DpC9OIVeMPJUhu44.TGcOYYRWnhwXmoFf6dC', 3, 'guest@example.com');

-- 기본 게시글 추가 (권한별 구분)
INSERT IGNORE INTO posts (title, content, author, is_admin_only) VALUES
('웹 보안 실습 플랫폼에 오신 것을 환영합니다', '이 플랫폼은 OWASP Top 10 취약점을 안전하게 실습할 수 있는 환경을 제공합니다.', 'admin', FALSE),
('권한별 계정 안내', '관리자: admin/admin123, 사용자: user/user123, 게스트: guest/guest123', 'admin', FALSE),
('[관리자 전용] 시스템 관리 공지', '이 게시글은 관리자만 볼 수 있습니다. 시스템 보안 설정 및 관리 사항을 다룹니다.', 'admin', TRUE),
('일반 사용자 게시글', '일반 사용자가 작성한 게시글입니다. 모든 권한의 사용자가 볼 수 있습니다.', 'user', FALSE),
('게스트 안내', '게스트는 읽기 전용으로만 사용할 수 있습니다.', 'admin', FALSE);