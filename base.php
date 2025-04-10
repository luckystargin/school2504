<?php

if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    die('禁止存取');
}

require_once 'database.php';


// 回復JSON訊息
// state: 狀態(成功或失敗) message:訊息內容 data:回傳資料(資料可有可無)
function respond($state, $message, $data = null)
{
    echo json_encode(["state" => $state, "message" => $message, "data" => $data]);
}

/**
 * 驗證API使用者ID
 * @param boolean $isAdmin 是否為管理員
 */
function validate_api_user_id($isAdmin = false)
{
    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
    if (!isset($headers['api-user-id']) || empty($headers['api-user-id'])) {
        http_response_code(401);
        respond(false, "未授權的存取");
        exit;
    }

    $api_user_id = $headers['api-user-id'];
    $conn = create_connection();
    $stmt = $conn->prepare("SELECT * FROM member WHERE Uid01 = ?");
    $stmt->bind_param("s", $api_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data) {
        http_response_code(401);
        respond(false, "未授權的存取");
        exit;
    }

    if ($isAdmin && $data['Username'] != 'admin') {
        http_response_code(401);
        respond(false, "未授權的存取");
        exit;
    }

    $GLOBALS['user'] = $data;
}

/**
 * 產生隨機碼
 * @param int $length 長度
 * @return string 隨機碼
 */
function generate_random_code($length = 12)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $characters_length = strlen($characters);
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, $characters_length - 1)];
    }
    return $random_string;
}


// 取得JSON的資料
function get_json_input()
{
    $data = file_get_contents("php://input");
    return json_decode($data, true);
}
