function toggleMenu(element) {
    // 1. Tìm submenu (thẻ ul) ngay sau thẻ li vừa click
    const submenu = element.nextElementSibling;

    if (submenu && submenu.classList.contains('submenu')) {
        // Kiểm tra trạng thái hiển thị thực tế
        const isHidden = window.getComputedStyle(submenu).display === "none";

        // 2. ĐÓNG TẤT CẢ các menu khác trước khi mở cái mới (Hiệu ứng Accordion)
        document.querySelectorAll('.submenu').forEach(el => {
            el.style.display = 'none';
        });
        document.querySelectorAll('.sidebar-menu li').forEach(li => {
            li.classList.remove('active'); // Gỡ bỏ xoay mũi tên của các mục khác
        });

        // 3. Xử lý mục vừa được click
        if (isHidden) {
            submenu.style.display = "block";
            element.classList.add('active'); // THÊM CLASS ĐỂ XOAY MŨI TÊN
        } else {
            submenu.style.display = "none";
            element.classList.remove('active'); // GỠ CLASS ĐỂ XOAY VỀ CŨ
        }
    }
}

// Giữ nguyên hàm open/close cũ
function openMenu() {
    document.getElementById("sidebar").classList.add("active");
    document.getElementById("overlay").classList.add("active");
    document.body.style.overflow = "hidden";
}

function closeMenu() {
    document.getElementById("sidebar").classList.remove("active");
    document.getElementById("overlay").classList.remove("active");
    document.body.style.overflow = "auto";
}