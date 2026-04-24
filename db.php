<?php

//  CATERING SERVICE SYSTEM — Database Connection & Helpers
//  File: db.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // change for production
define('DB_NAME', 'catering_db');
define('DB_PORT', 3306);

function getConnection(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($conn->connect_error) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// ---------------------------------------------------------------
// Safe query helper — returns mysqli_result or true/false
// ---------------------------------------------------------------
function dbQuery(string $sql, array $params = [], string $types = '') {
    $conn = getConnection();

    if (empty($params)) {
        $result = $conn->query($sql);
        if ($result === false) {
            error_log('DB Error: ' . $conn->error . ' | SQL: ' . $sql);
        }
        return $result;
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $conn->error . ' | SQL: ' . $sql);
        return false;
    }

    if (empty($types)) {
        $types = str_repeat('s', count($params));
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result ?? true;
}

// Fetch all rows as assoc array
function dbFetchAll(string $sql, array $params = [], string $types = ''): array {
    $result = dbQuery($sql, $params, $types);
    if ($result instanceof mysqli_result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

// Fetch single row
function dbFetchOne(string $sql, array $params = [], string $types = ''): ?array {
    $result = dbQuery($sql, $params, $types);
    if ($result instanceof mysqli_result) {
        return $result->fetch_assoc() ?? null;
    }
    return null;
}

// Execute INSERT / UPDATE / DELETE, return affected rows
function dbExecute(string $sql, array $params = [], string $types = ''): int {
    $conn = getConnection();

    if (empty($params)) {
        $conn->query($sql);
        return $conn->affected_rows;
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) return -1;

    if (empty($types)) {
        $types = str_repeat('s', count($params));
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}

// Get last inserted ID
function dbLastId(): int {
    return (int) getConnection()->insert_id;
}
