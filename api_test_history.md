# API Test History

This document records the steps taken to test the API.

## 1. Start the services

The services were started using `docker-compose`.

```bash
docker-compose up -d
```

## 2. Register a new user

A new user was registered using the `register` endpoint.

```bash
curl -X POST -H "Content-Type: application/json" -d "{\"action\": \"register\", \"username\": \"testuser\", \"password\": \"testpassword\"}" http://localhost/api/auth.php
```

**Response:**
```json
{"status":"success","message":"User registered successfully"}
```

## 3. Log in as the new user

The user was logged in using the `login` endpoint.

```bash
curl -X POST -H "Content-Type: application/json" -d "{\"action\": \"login\", \"username\": \"testuser\", \"password\": \"testpassword\"}" http://localhost/api/auth.php
```

**Response:**
```json
{"status":"success","message":"Login successful","token":"dummy_jwt_token"}
```

## 4. Create a new post

A new post was created using the `board` endpoint.

```bash
curl -X POST -H "Content-Type: application/json" -d "{\"username\": \"testuser\", \"content\": \"This is a test post.\"}" http://localhost/api/board.php
```

**Response:**
```json
{"status":"success","message":"Post created successfully"}
```

## 5. Get all posts

All posts were retrieved from the `board` endpoint.

```bash
curl http://localhost/api/board.php
```

**Response:**
```json
{"status":"success","data":[{"id":"1","username":"tester0","content":"1111"},{"id":"2","username":"test","content":"123123213"},{"id":"3","username":"testuser","content":"This is a test post."}]}
```
