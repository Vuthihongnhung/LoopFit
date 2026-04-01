// 1. CHỨC NĂNG MENU (GIỮ NGUYÊN CỦA BẠN)
function openMenu() {
    document.getElementById("sidebar").style.left = "0";
    document.getElementById("overlay").style.display = "block";
}

function closeMenu() {
    document.getElementById("sidebar").style.left = "-350px";
    document.getElementById("overlay").style.display = "none";
}

// 2. CHỨC NĂNG LẤY DỮ LIỆU TỪ DATABASE (THÊM MỚI)
async function loadProducts() {
    try {
        // Gọi đến file PHP của bạn ở port 8080
        const response = await fetch('http://localhost:8080/loopfit/admin/products/index.php');
        const result = await response.json();

        if (result.success) {
            const productGrid = document.getElementById('product-list');
            let html = '';

            // Duyệt qua dữ liệu từ Database đổ vào khung HTML
            result.data.forEach(p => {
                html += `
                    <div class="product-card" data-category="${p.category.id}">
                        <img src="http://localhost:8080/loopfit${p.image}" alt="${p.name}">
                        <h3>${p.name}</h3>
                        <p>Giá: ${new Intl.NumberFormat('vi-VN').format(p.price)}đ</p>
                        <p>Loại: ${p.category.name}</p>
                        <button>MUA NGAY</button>
                    </div>
                `;
            });

            productGrid.innerHTML = html;

            // Sau khi load xong sản phẩm, mới chạy được logic Filter bên dưới
            setupFilter();
        }
    } catch (error) {
        console.error("Lỗi kết nối port 8080:", error);
    }
}

// 3. CHỨC NĂNG FILTER (SỬA LẠI ĐỂ CHẠY SAU KHI LOAD DATA)
function setupFilter() {
    const buttons = document.querySelectorAll(".filter-btn");
    const cards = document.querySelectorAll(".product-card");

    buttons.forEach(btn => {
        btn.onclick = () => {
            buttons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            const filterId = btn.getAttribute("data-filter"); // Ví dụ: 1, 2, 3 (ID của Category)

            cards.forEach(card => {
                const categoryId = card.getAttribute("data-category");
                if (filterId === "all" || categoryId === filterId) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        };
    });
}

// CHẠY HÀM KHI TRANG WEB TẢI XONG
loadProducts();