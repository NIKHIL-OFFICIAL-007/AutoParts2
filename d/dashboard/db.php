<?php
// db.php - Database Connection Manager

// Database Configuration
$dbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'autoparts',
    'charset' => 'utf8mb4'
];

/**
 * MySQLi Connection (your current implementation)
 */
$conn = new mysqli(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['database']
);

// Check MySQLi connection
if ($conn->connect_error) {
    error_log("MySQLi Connection Error: " . $conn->connect_error);
    die("We're experiencing technical difficulties. Please try again later.");
}

// Set charset
$conn->set_charset($dbConfig['charset']);

/**
 * PDO Connection (needed for your dashboard.php)
 */
try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false
        ]
    );
} catch (PDOException $e) {
    error_log("PDO Connection Error: " . $e->getMessage());
    die("We're experiencing technical difficulties. Please try again later.");
}

/**
 * Utility function to check database connections
 */
function checkDatabaseConnections() {
    global $conn, $pdo;
    
    $status = [
        'mysqli' => $conn ? $conn->ping() : false,
        'pdo' => $pdo ? true : false
    ];
    
    return $status;
}

// Error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Close connections at script end (PHP will handle this automatically, but explicit is good)
register_shutdown_function(function() {
    global $conn;
    if ($conn instanceof mysqli) {
        $conn->close();
    }
});