<?php

if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    die('禁止存取');
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH");
header("Access-Control-Allow-Headers: *");
header('Content-Type: application/json; charset=utf-8');

// 預檢請求要回應 200，瀏覽器才認可
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
