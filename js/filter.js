// Lấy tất cả nút filter
const buttons = document.querySelectorAll(".filter-btn");

// Lấy tất cả sản phẩm
const products = document.querySelectorAll(".product");

// Lặp qua từng nút
buttons.forEach(btn => {
    btn.addEventListener("click", () => {

        // Xóa active ở tất cả nút
        buttons.forEach(b => b.classList.remove("active"));

        // Thêm active cho nút được click
        btn.classList.add("active");

        // Lấy giá trị filter (all / nam / nu)
        const filter = btn.getAttribute("data-filter");

        // Lọc sản phẩm
        products.forEach(item => {

            const category = item.getAttribute("data-category");

            if (filter === "all" || category === filter) {
                item.style.display = "block"; // hiện
            } else {
                item.style.display = "none"; // ẩn
            }
        });
    });
});