<?php

define('SECURE_ACCESS', true);

require_once 'cors.php';
require_once 'database.php';
require_once 'base.php';


function get_all()
{
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    $conn = create_connection();
    $stmt = $conn->prepare("SELECT order2.*, member.Username FROM order2 LEFT JOIN member ON order2.user_id = member.ID ORDER BY `created_at` DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    // 獲取總數以計算總頁數
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM order2");
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total / $limit);

    respond(true, "取得所有訂單資料", [
        'data' => $data,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total,
            'limit' => $limit,
        ],
    ]);
}

function get_all_by_user()
{
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    $user = $GLOBALS['user'];
    $conn = create_connection();
    $stmt = $conn->prepare("SELECT * FROM order2 WHERE user_id = ? ORDER BY `created_at` DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $user['ID'], $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    // 獲取總數以計算總頁數
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM order2 WHERE user_id = ?");
    $count_stmt->bind_param("s", $user['ID']);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total / $limit);


    respond(true, "取得該會員訂單資料", [
        'data' => $data,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total,
            'limit' => $limit,
        ],
    ]);
}

// 尋找使用者
function findUser($id)
{
    $conn = create_connection();
    $stmt = $conn->prepare("SELECT * FROM member WHERE ID = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data;
}

// 尋找產品
function findProduct($id)
{
    $conn = create_connection();
    $stmt = $conn->prepare("SELECT * FROM products WHERE UID = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data;
}

// 尋找訂單
function findOrder($id)
{
    $conn = create_connection();
    $stmt = $conn->prepare("SELECT * FROM order2 WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data;
}

// '新芽','小草','小花','大樹','後台管理者'
function getDiscount($level)
{
    return match ($level) {
        '小草' => '0.9',
        '小花' => '0.8',
        '大樹' => '0.7',
        default => '1',
    };
}

function create()
{
    $input = get_json_input();
    error_log(json_encode($input));

    if (empty($input['products_id']) || empty($input['quantity'])) {
        return respond(false, "請填寫完整資料");
    }

    // 驗證數量
    if (!is_numeric($input['quantity']) || $input['quantity'] <= 0) {
        return respond(false, "數量不正確");
    }

    $user = $GLOBALS['user'];

    $product = findProduct($input['products_id']);
    if (!$product) {
        return respond(false, "產品不存在");
    }

    $orderId = generate_random_code();
    $discount = getDiscount($user['Level']);

    // 金額計算
    $amounts = calculate_order_amount($product['amount'], $input['quantity'], $discount);
    $original_amount = $amounts['original_amount'];
    $discount_amount = $amounts['discount_amount'];
    $amount = $amounts['amount'];

    $conn = create_connection();
    $stmt = $conn->prepare("INSERT INTO order2 (id, user_id, level, products_id, quantity, unit_price, discount, original_amount, discount_amount, amount) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssssss", $orderId, $user['ID'], $user['Level'], $input['products_id'], $input['quantity'], $product['amount'], $discount, $original_amount, $discount_amount, $amount);

    if ($stmt->execute()) {
        respond(true, "新增訂單成功");
    } else {
        respond(false, "新增訂單失敗");
    }
}

// 計算訂單金額
function calculate_order_amount($product_amount, $quantity, $discount)
{
    $original_amount = bcmul($product_amount, $quantity);
    $discount_value = bcsub('1', $discount, 2);
    $discount_amount = bcmul($original_amount, $discount_value);
    $amount = bcsub($original_amount, $discount_amount);
    return [
        'original_amount' => $original_amount,
        'discount_amount' => $discount_amount,
        'amount' => $amount,
    ];
}

/**
 * 更新訂單
 */
function update_order()
{
    $input = get_json_input();
    error_log(json_encode($input));

    if (empty($input['id']) || empty($input['quantity']) || empty($input['discount'])) {
        return respond(false, "請填寫完整資料");
    }

    // 驗證數量
    if (!is_numeric($input['quantity']) || $input['quantity'] <= 0) {
        return respond(false, "數量不正確");
    }

    $order = findOrder($input['id']);
    if (!$order) {
        return respond(false, "訂單不存在");
    }

    $product = findProduct($order['products_id']);
    if (!$product) {
        return respond(false, "產品不存在");
    }

    // 金額計算
    $amounts = calculate_order_amount($product['amount'], $input['quantity'], $input['discount']);
    $original_amount = $amounts['original_amount'];
    $discount_amount = $amounts['discount_amount'];
    $amount = $amounts['amount'];

    $conn = create_connection();
    $stmt = $conn->prepare("UPDATE order2 SET quantity = ?, original_amount = ?, discount_amount = ?, amount = ?, discount = ? WHERE id = ?");
    try {
        $stmt->bind_param("ssssss", $input['quantity'], $original_amount, $discount_amount, $amount, $input['discount'], $input['id']);

        if ($stmt->execute()) {
            respond(true, "更新訂單成功");
        } else {
            respond(false, "更新訂單失敗");
        }
    } catch (Exception $e) {
        respond(false, "系統發生錯誤，請稍後再試");
    } finally {
        $stmt->close();
        $conn->close();
    }
}

/**
 * 取消訂單
 */
function cancel()
{
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        return respond(false, "缺少訂單 id");
    }

    $order_id = trim($_GET['id']);

    $conn = create_connection();
    $stmt = $conn->prepare("UPDATE order2 SET status = -1 WHERE id = ?");
    try {
        $stmt->bind_param("s", $order_id);

        if (!$stmt->execute()) {
            return respond(false, "取消訂單失敗");
        }

        if ($stmt->affected_rows !== 1) {
            return respond(false, "找不到此訂單或訂單已取消");
        }

        return respond(true, "訂單取消成功");
    } catch (Exception $e) {
        return respond(false, "系統發生錯誤，請稍後再試");
    } finally {
        $stmt->close();
        $conn->close();
    }
}



switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'get_all':
                validate_api_user_id(true);
                get_all();
                break;
            case 'get_all_by_user':
                validate_api_user_id();
                get_all_by_user();
                break;
            default:
                respond(false, "不支援的操作");
                break;
        }
        break;

    case 'POST':
        $action = $_GET['action'] ?? '';
        if (empty($action)) {
            respond(false, "未指定操作");
            break;
        }

        switch ($action) {
            case 'update_order':
                validate_api_user_id(true);
                update_order();
                break;

            case 'create':
                validate_api_user_id();
                create();
                break;
        }
        break;
    case 'PUT':
        break;
    case 'DELETE':
        $action = $_GET['action'] ?? '';
        if (empty($action)) {
            respond(false, "未指定操作");
            break;
        }

        switch ($action) {
            case 'cancel':
                validate_api_user_id(true);
                cancel();
                break;
            default:
                respond(false, "不支援的操作");
                break;
        }
        break;
    default:
        http_response_code(404);
        respond(false, "不支援的HTTP方法");
        break;
}
