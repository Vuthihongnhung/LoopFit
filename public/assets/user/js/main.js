function toggleMenu(element) {
    const submenu = element.nextElementSibling;

    if (!submenu || !submenu.classList.contains('submenu')) return;

    const isOpen = submenu.classList.contains('active'); // 🔥 check TRƯỚC

    // đóng tất cả
    document.querySelectorAll('.submenu').forEach(el => {
        el.classList.remove('active');
    });

    document.querySelectorAll('.sidebar-menu li').forEach(li => {
        li.classList.remove('active');
    });

    // nếu trước đó đang mở → KHÔNG mở lại => sẽ đóng
    if (!isOpen) {
        submenu.classList.add('active');
        element.classList.add('active');
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

// =====================
// PRODUCT DETAIL UI
// =====================

window.changeImg = function (el) {
    const img = document.getElementById("mainImg");
    if (img) img.src = el.src;
};

window.increase = function () {
    const qty = document.getElementById("qty");
    if (!qty) return;

    qty.value = parseInt(qty.value || 1) + 1;
};

window.decrease = function () {
    const qty = document.getElementById("qty");
    if (!qty) return;

    let val = parseInt(qty.value || 1);
    if (val > 1) qty.value = val - 1;
};

let selectedSize = null;

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('size-btn')) {

        document.querySelectorAll('.size-btn')
            .forEach(btn => btn.classList.remove('active'));

        e.target.classList.add('active');

        selectedSize = e.target.dataset.size || e.target.innerText.trim();
    }
});