<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_taskmanager');

function getConnection() {
    static $conn = null; // keep one persistent connection
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('Database connection failed: ' . $conn->connect_error);
        }
    }
    return $conn;
}

