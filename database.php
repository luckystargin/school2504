<?php

if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    die('禁止存取');
}

// const定義後不更動
const DB_SERVER   = "xxx";
const DB_USERNAME = "xxx";
const DB_PASSWORD = "xxx";
const DB_NAME     = "art";


// 建立連線
function create_connection()
{
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if (!$conn) {
        echo json_encode(["state" => false, "message" => "連線失敗!"]);
        exit;
    }
    return $conn;
}