<?php
define('SECURE_ACCESS', true);

require_once 'cors.php';
require_once 'database.php';
require_once 'base.php';

function get_all()
{
    $conn = create_connection();
    $sql = "SELECT * FROM products";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

    foreach ($data as &$item) {
        if (isset($item['showInfo'])) {
            $item['showInfo'] = json_decode($item['showInfo'], true);
        }

        if (isset($item['masterUnit'])) {
            $item['masterUnit'] = json_decode($item['masterUnit'], true);
        }
        if (isset($item['subUnit'])) {
            $item['subUnit'] = json_decode($item['subUnit'], true);
        }
        if (isset($item['supportUnit'])) {
            $item['supportUnit'] = json_decode($item['supportUnit'], true);
        }
        if (isset($item['otherUnit'])) {
            $item['otherUnit'] = json_decode($item['otherUnit'], true);
        }
    }
    respond(true, "取得所有產品資料", $data);
}

function get_by_id()
{
    $id = $_GET['id'] ?? '';
    if ($id == '') {
        respond(false, "未提供ID");
        exit;
    }
    $conn = create_connection();
    $sql = "SELECT * FROM products WHERE `UID` = '$id'";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);
    if ($data) {
        if (isset($data['showInfo'])) {
            $data['showInfo'] = json_decode($data['showInfo'], true);
        }

        if (isset($data['masterUnit'])) {
            $data['masterUnit'] = json_decode($data['masterUnit'], true);
        }
        if (isset($data['subUnit'])) {
            $data['subUnit'] = json_decode($data['subUnit'], true);
        }
        if (isset($data['supportUnit'])) {
            $data['supportUnit'] = json_decode($data['supportUnit'], true);
        }
        if (isset($data['otherUnit'])) {
            $data['otherUnit'] = json_decode($data['otherUnit'], true);
        }
    }
    respond(true, "取得產品資料", $data);
}


switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'get_all':
                get_all();
                break;
            case 'get_by_id':
                get_by_id();
                break;
            default:
                respond(false, "不支援的操作");
                break;
        }
        break;
    case 'POST':
        break;
    case 'PUT':
        break;
    case 'DELETE':
        break;
    default:
        respond(false, "不支援的HTTP方法");
        break;
}
