<?php

define('SECURE_ACCESS', true);

require_once 'cors.php';
require_once 'database.php';
require_once 'base.php';

// 計算訂單金額總額，前30天的訂單
function get_order_total()
{
    $conn = create_connection();
    $sql = "SELECT DATE_FORMAT(`created_at`, '%Y-%m-%d') AS `date`, SUM(`amount`) AS `total` FROM `order2` GROUP BY `date` ORDER BY `date` DESC LIMIT 30";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    usort($data, function ($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });
    // 將資料轉成 {label:[],data:[]} 的格式，
    $labels = array_column($data, 'date');
    $dataSet = array_map('floatval', array_column($data, 'total'));
    $data = [
        'labels' => $labels,
        'data' => $dataSet
    ];
    respond(true, "取得訂單金額總額", $data);
}

// 計數每日新增會員數量，前30天的會員
function get_member_count()
{
    $conn = create_connection();
    $sql = "SELECT DATE_FORMAT(`Created_at`, '%Y-%m-%d') AS `date`, COUNT(`ID`) AS `count` FROM `member` GROUP BY `date` ORDER BY `date` DESC LIMIT 30";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    usort($data, function ($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });
    // 將資料轉成 {label:[],data:[]} 的格式，
    $labels = array_column($data, 'date');
    $dataSet = array_map('intval', array_column($data, 'count'));
    $data = [
        'labels' => $labels,
        'data' => $dataSet
    ];
    respond(true, "取得會員數量", $data);
}

// 計數訂單的等級分佈
function get_order_level_count()
{
    $conn = create_connection();
    $sql = "SELECT `level`, COUNT(`level`) AS `count` FROM `order2` GROUP BY `level`";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    // 將資料轉成 {label:[],data:[]} 的格式，
    $labels = array_column($data, 'level');
    $dataSet = array_map('intval', array_column($data, 'count'));
    $data = [
        'labels' => $labels,
        'data' => $dataSet
    ];
    respond(true, "取得訂單的等級分佈", $data);
}

// 查詢會員的縣市分佈前五名縣市
function get_top_cities()
{
    $conn = create_connection();
    $sql = "SELECT `City`, COUNT(`City`) AS `count` FROM `member` GROUP BY `City` ORDER BY `count` DESC LIMIT 5";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // 將資料轉成 {label:[],data:[]} 的格式，
    $labels = array_column($data, 'City');
    $dataSet = array_map('intval', array_column($data, 'count'));
    $data = [
        'labels' => $labels,
        'data' => $dataSet
    ];
    respond(true, "取得會員的縣市分佈前五名縣市", $data);
}


switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'get_order_total':
                validate_api_user_id(true);
                get_order_total();
                break;
            case 'get_member_count':
                validate_api_user_id(true);
                get_member_count();
                break;
            case 'get_order_level_count':
                validate_api_user_id(true);
                get_order_level_count();
                break;
            case 'get_top_cities':
                validate_api_user_id(true);
                get_top_cities();
                break;
            default:
                respond(false, "不支援的操作");
                break;
        }
        break;
    default:
        respond(false, "不支援的HTTP方法");
        break;
}
