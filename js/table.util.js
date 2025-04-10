let currentLimit = 10;

/**
 * 設定瀏覽器標籤上的文字
 * @param {string} text
 * @param {string} color
 */
function setBadge(text, color) {
  // TODO 動態取得顏色標籤
  switch (color) {
    case "primary":
      return `<span class="badge bg-primary">${text}</span>`;
    case "secondary":
      return `<span class="badge bg-secondary">${text}</span>`;
    case "success":
      return `<span class="badge bg-success">${text}</span>`;
    case "danger":
      return `<span class="badge bg-danger">${text}</span>`;
    case "warning":
      return `<span class="badge bg-warning">${text}</span>`;
    case "info":
      return `<span class="badge bg-info">${text}</span>`;
    case "light":
      return `<span class="badge bg-light text-dark">${text}</span>`;
    case "dark":
      return `<span class="badge bg-dark">${text}</span>`;
    default:
      return `<span class="badge bg-primary">${text}</span>`;
  }
}


/**
 * 渲染分頁
 * @param {object} pagination
 */
function renderPagination(pagination) {
  const { current_page, total_pages } = pagination;
  let paginationHTML = `
      <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center mt-4">
    `;

  const pageRange = 2;

  // 上一頁按鈕
  paginationHTML += `
      <li class="page-item ${current_page === 1 ? "disabled" : ""}">
        <a class="page-link" href="#" data-page="${current_page - 1
    }">«</a>
      </li>
    `;

  if (total_pages <= 5) {
    // 顯示全部頁碼
    for (let i = 1; i <= total_pages; i++) {
      paginationHTML += `
          <li class="page-item ${i === current_page ? "active" : ""}">
            <a class="page-link" href="#" data-page="${i}">${i}</a>
          </li>
        `;
    }
  } else {
    // 第一頁
    paginationHTML += `
        <li class="page-item ${current_page === 1 ? "active" : ""}">
          <a class="page-link" href="#" data-page="1">1</a>
        </li>
      `;

    // 前省略號
    if (current_page - pageRange > 2) {
      paginationHTML += `
          <li class="page-item disabled"><span class="page-link">...</span></li>
        `;
    }

    // 中間頁碼
    for (
      let i = Math.max(2, current_page - pageRange);
      i <= Math.min(total_pages - 1, current_page + pageRange);
      i++
    ) {
      paginationHTML += `
          <li class="page-item ${i === current_page ? "active" : ""}">
            <a class="page-link" href="#" data-page="${i}">${i}</a>
          </li>
        `;
    }

    // 後省略號
    if (current_page + pageRange < total_pages - 1) {
      paginationHTML += `
          <li class="page-item disabled"><span class="page-link">...</span></li>
        `;
    }

    // 最後一頁

    paginationHTML += `
        <li class="page-item ${current_page === total_pages ? "active" : ""
      }">
          <a class="page-link" href="#" data-page="${total_pages}">${total_pages}</a>
        </li>
      `;
  }

  // 下一頁按鈕
  paginationHTML += `
      <li class="page-item ${current_page === total_pages ? "disabled" : ""
    }">
        <a class="page-link" href="#" data-page="${current_page + 1
    }">»</a>
      </li>
    `;

  paginationHTML += `
        </ul>
      </nav>
    `;

  $("#pagination").html(paginationHTML);

  // 點擊分頁按鈕事件
  $(".page-link").click(function (e) {
    e.preventDefault();
    const page = $(this).data("page");

    if (page === current_page) return;

    if (page >= 1 && page <= total_pages) {
      setOrderData(page, currentLimit);
      $("html, body").animate(
        { scrollTop: $(".table").offset().top - 70 },
        300
      );
    }
  });
}

$(document).ready(function () {
  // 每頁筆數選擇
  $("#perPageSelect").change(function () {
    currentLimit = $(this).val();
    setOrderData(1, currentLimit);
  });
});