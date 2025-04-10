# 藝文活動購票平台（artevents）

這是一個以 PHP 為後端核心的票券購買平台，整合台灣地區的藝文活動資訊，提供會員購票、訂單管理與圖表分析等功能。  
使用者可以輕鬆瀏覽活動、購買票券，管理者亦可透過後台進行會員與訂單的管理。

---

## 專案技術

- **前端**：
  - HTML
  - CSS
  - JavaScript
  - Bootstrap v5.2.3
  - wow.js
  - Animate v4.1.1
  - chart.js
  - jQuery v3.7.1

- **後端**：
  - PHP

- **資料庫**：
  - MySQL

- **其他技術**：
  - Git 版本控制
  - 串接台灣縣市鄉鎮 API
  - 串接台灣藝文活動 API（下載 JSON → 匯入資料庫 → 自建 API）

---

## 功能介紹

### 1. 訪客 / 會員功能

- 會員登入系統
- 購買票券功能
- 會員查看歷史訂單

![歷史訂單圖片](https://github.com/luckystargin/school2504/blob/main/images/Snipaste_2025-04-09_17-48-45.png)

                                     篩選各縣市藝文活動

![歷史訂單圖片](https://github.com/luckystargin/school2504/blob/main/images/Snipaste_2025-04-09_17-50-37.png)
                                     購買票券

![歷史訂單圖片](https://github.com/luckystargin/school2504/blob/main/images/Snipaste_2025-04-10_11-15-22.png)
                                     會員瀏覽訂單


---

### 2. 管理者後台功能

#### a. 會員管理

- 查看會員帳號狀態（啟用 / 停用）
- 會員資料 CRUD（建立、讀取、更新、刪除）
- 停權會員功能

   ![歷史訂單圖片](https://github.com/luckystargin/school2504/blob/main/images/Snipaste_2025-04-09_17-54-51.png)
**會員管理**
  
   ![歷史訂單圖片](https://github.com/luckystargin/school2504/blob/main/images/Snipaste_2025-04-10_11-31-59.png)
**會員更新**

#### b. 訂單管理

- 對應活動 API 的活動編號
- 計算會員折扣與商品金額
- 訂單資料 CRUD（建立、讀取、更新、刪除）
- 取消訂單

    ![歷史訂單圖片](https://github.com/luckystargin/school2504/blob/main/images/Snipaste_2025-04-09_17-54-28.png)
**訂單管理**

   ![歷史訂單圖片](https://github.com/luckystargin/school2504/blob/main/images/Snipaste_2025-04-10_11-33-01.png)
**訂單更新**

#### c. 圖表分析

- 每日訂單總額（近 30 天）
- 每日新增會員數（近 30 天）
- 訂單量與會員等級關聯分析
- 會員居住地分布

  ![歷史訂單圖片](https://github.com/luckystargin/school2504/blob/main/images/Snipaste_2025-04-09_17-51-44.png)
**上:每日訂單總額 下:每日新增會員數**

---

### 3. 其他附加功能

- RWD 響應式網站
- 全台縣市鄉鎮選單
- 全台藝文活動選單

---

## 建置流程

（此部分可以根據實際建置過程填寫）

---

## 聯絡作者

如有任何問題或合作需求，請聯絡：  
- **Email**: angel11432000@gmail.com
