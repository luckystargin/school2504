<?php

define('SECURE_ACCESS', true);

require_once 'cors.php';
require_once 'database.php';
require_once 'base.php';


// 會員註冊

function register_user()
{
    $input = get_json_input();
    error_log(json_encode($input));


    if (!isset($input["username"], $input["password"], $input["email"], $input["gender"], $input["age"], $input["city"], $input["income"])) {
        respond(false, "欄位錯誤");
        return;
    }

    $p_username = trim($input["username"]);
    $p_password = password_hash(trim($input["password"]), PASSWORD_DEFAULT);
    $p_email    = trim($input["email"]);
    $p_gender   = trim($input["gender"]);
    $p_age      = trim($input["age"]);
    $p_city     = trim($input["city"]);
    $p_income   = trim($input["income"]);

    if (empty($p_username) || empty($p_password) || empty($p_email) || empty($p_gender) || empty($p_age) || empty($p_city) || empty($p_income)) {
        respond(false, "欄位不得為空");
        return;
    }

    $conn = create_connection();


    $stmtcheckUser = $conn->prepare("SELECT Username FROM member WHERE Username = ?");
    $stmtcheckUser->bind_param("s", $p_username); //一定要傳遞變數
    $stmtcheckUser->execute();
    $resultCheck = $stmtcheckUser->get_result();


    if ($resultCheck->num_rows === 1) {
        // 帳號已存在
        respond(false, "帳號已存在，不可使用");
        $stmtcheckUser->close();
        $conn->close();
        return;
    }
    $stmtcheckUser->close();

    $stmt = $conn->prepare("INSERT INTO `member`(Username, Password, Email, Gender, Age, City, Income) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $p_username, $p_password, $p_email, $p_gender, $p_age, $p_city, $p_income);

    if ($stmt->execute()) {
        respond(true, "註冊成功");
    } else {
        respond(false, "註冊失敗");
    }
    $stmt->close();
    $conn->close();
}

// 會員登入
function login_user()
{
    $input = get_json_input();
    if (isset($input["username"], $input["password"])) {
        $p_username = trim($input["username"]);
        $p_password = trim($input["password"]);
        if ($p_username && $p_password) {
            $conn = create_connection();

            $stmt = $conn->prepare("SELECT * FROM member WHERE Username = ? AND status = 1");
            $stmt->bind_param("s", $p_username); //一定要傳遞變數
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();

             
                if (password_verify($p_password, $row["Password"])) {
                  
                    // 第二次，產生UID並更新至資料庫
                    $uid01 = substr(uniqid(), 2, 4) . substr(hash('sha512', time()), 2, 4);

                    //避免 injection攻擊
                    $update_stmt = $conn->prepare("UPDATE member SET Uid01 = ? WHERE Username = ?");
                    $update_stmt->bind_param('ss', $uid01, $p_username);

                    if ($update_stmt->execute()) {
                     
                        // 第三次，取得登入的使用者資訊
                        $user_stmt = $conn->prepare("SELECT Username,Email,Uid01,Created_at FROM member WHERE Username = ?");
                        $user_stmt->bind_param("s", $p_username);
                        $user_stmt->execute();

                
                        $user_data = $user_stmt->get_result()->fetch_assoc();
                        respond(true, "登入成功", $user_data);
                    } else {
                        respond(false, "UID更新失敗，登入失敗!");
                    }
                } else {
                    respond(false, "密碼錯誤，登入失敗!");
                }
            } else {
                respond(false, "該帳號不存在，登入失敗!");
            }

            $stmt->close();
            $conn->close();
        } else {
            respond(false, "欄位不得為空，登入失敗!");
        }
    } else {
        respond(false, "欄位錯誤");
    }
}


// Uid01驗證
function check_uid()
{
    $input = get_json_input();
    if (isset($input["uid01"])) {
        $p_uid01 = trim($input["uid01"]);
        if ($p_uid01) {

         
            $conn = create_connection();

            $stmt = $conn->prepare("SELECT * FROM member WHERE Uid01 = ? AND status = 1");
            $stmt->bind_param("s", $p_uid01); 
            $stmt->execute();
            $result = $stmt->get_result();


            if ($result->num_rows === 1) {
                // 驗證成功
                $userdata = $result->fetch_assoc();
                respond(true, "驗證成功", $userdata);
            } else {
                respond(false, "驗證失敗!");
            }



            $stmt->close();
            $conn->close();
        } else {
            respond(false, "欄位不得為空!");
        }
    } else {
        respond(false, "欄位錯誤!");
    }
}


/**
 * @description 驗證帳號是否已經存在(給註冊頁面使用)
 */
function check_uni_username()
{
    $input = get_json_input();
    if (!isset($input["username"])) {
        return respond(false, "欄位錯誤!");
    }
    $p_username = trim($input["username"]);

    if (!$p_username) {
        return respond(false, "欄位不得為空!");
    }

    $conn = create_connection();

    $stmt = $conn->prepare("SELECT Username FROM member WHERE Username = ?");
    $stmt->bind_param("s", $p_username); 
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows === 1) {
        // 帳號已存在
        respond(false, "帳號已存在，不可使用");
    } else {
        // 帳號不存在
        respond(true, "帳號不存在，可以使用");
    }



    $stmt->close();
    $conn->close();
}



// 取得所有會員資料
function get_all_user_data()
{

    $conn = create_connection();

 
    $stmt = $conn->prepare("SELECT * FROM member ORDER BY ID ASC");
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows > 0) {
        $mydata = array();
        while ($row = $result->fetch_assoc()) {
            unset($row["Password"]);
            unset($row["Uid01"]);
       
            $mydata[] = $row;
        }
        respond(true, "取得所有會員資料成功", $mydata);
    } else {
        // 查無資料
        respond(false, "查無資料");
    }
    $stmt->close();
    $conn->close();
}

// 會員更新
function update_user()
{
    $input = get_json_input();
    if (isset($input["id"], $input["level"], $input["email"], $input["status"])) {
   
        $p_id = trim($input["id"]);
        $p_level = trim($input["level"]);
        $p_email = trim($input["email"]);
        $p_status = trim($input["status"]);
        if ($p_id && $p_level && $p_email && $p_status) {
            $conn = create_connection();

            $stmt = $conn->prepare("UPDATE member SET Level = ?, Email = ?, status = ? WHERE ID = ?");
            $stmt->bind_param("sssi", $p_level, $p_email, $p_status, $p_id); //一定要傳遞變數

            if ($stmt->execute()) {
                if ($stmt->affected_rows === 1) {
                    respond(true, "會員更新成功!");
                } else {
                    respond(false, "無更新行為，會員更新失敗!");
                }
            } else {
                respond(false, "會員更新失敗!");
            }
            $stmt->close();
            $conn->close();
        } else {
            respond(false, "欄位不得為空!");
        }
    } else {
        respond(false, "欄位錯誤!");
    }
}


// 會員刪除
function delete_user()
{
    $input = get_json_input();
    if (isset($input["id"])) {
       
        $p_id = trim($input["id"]);
        if ($p_id) {

            $conn = create_connection();

            $stmt = $conn->prepare("DELETE FROM member WHERE ID = ?");
            $stmt->bind_param("i", $p_id); //一定要傳遞變數

            if ($stmt->execute()) {
                if ($stmt->affected_rows === 1) {
                    respond(true, "會員刪除成功!");
                } else {
                    respond(false, "無刪除行為，會員刪除失敗!");
                }
            } else {
                respond(false, "會員刪除失敗!");
            }
            $stmt->close();
            $conn->close();
        } else {
            respond(false, "欄位不得為空!");
        }
    } else {
        respond(false, "欄位錯誤!");
    }
}

// 將使用者的狀態改為停權 -1
function suspend_user()
{
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        return respond(false, "缺少會員 id");
    }

    $user_id = trim($_GET['id']);
    $conn = create_connection();
    $stmt = $conn->prepare("UPDATE member SET status = -1 WHERE ID = ?");
    try {
        $stmt->bind_param("s", $user_id);

        if (!$stmt->execute()) {
            return respond(false, "停權失敗");
        }

        if ($stmt->affected_rows !== 1) {
            return respond(false, "找不到此會員或會員已停權");
        }

        return respond(true, "會員停權成功");
    } catch (Exception $e) {
        return respond(false, "系統發生錯誤，請稍後再試");
    } finally {
        $stmt->close();
        $conn->close();
    }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
    $action = $_GET['action'] ?? '';
    switch ($action) {

        case 'register':
            register_user();
            break;

        case 'login':
            login_user();
            break;

        case 'checkuid':
            check_uid();
            break;
        case 'checkuni':
            check_uni_username();
            break;

        case 'update':
            update_user();
            break;


        default:
            respond(false, "無效的操作");
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    switch ($action) {
        case 'getalldata':
            get_all_user_data();
            break;
        default:
            respond(false, "無效的操作");
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $action = $_GET['action'] ?? '';
    switch ($action) {
        // case 'delete':
        //     delete_user();
        //     break;
        case 'suspend':
            suspend_user();
            break;
        default:
            respond(false, "無效的操作");
    }
} else {
    respond(false, "無效的請求方法");
}
