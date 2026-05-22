// SIDEBAR MENU (Accordion + xoay mũi tên)
function toggleMenu(element) {
    // Tìm phần tử li chứa menu item và submenu
    const li = element.closest('li');
    if (!li) return;
    const submenu = li.querySelector('.submenu');
    if (!submenu) return;

    const isActive = li.classList.contains('active');
    const arrow = element.querySelector('.arrow-icon, i.fa-chevron-down, i.fa-chevron-up, i.bi-chevron-down, i.bi-chevron-up');

    // Đóng tất cả các mục khác (chế độ accordion)
    document.querySelectorAll('.sidebar-menu > li').forEach(item => {
        if (item !== li) {
            item.classList.remove('active');
            const otherSub = item.querySelector('.submenu');
            if (otherSub) otherSub.classList.remove('active');
            const otherArrow = item.querySelector('.arrow-icon, i.fa-chevron-down, i.fa-chevron-up, i.bi-chevron-down, i.bi-chevron-up');
            if (otherArrow) {
                otherArrow.classList.remove('fa-chevron-up', 'bi-chevron-up');
                otherArrow.classList.add('fa-chevron-down', 'bi-chevron-down');
            }
        }
    });

    // Xử lý mục hiện tại
    if (isActive) {
        // Đang mở -> đóng lại
        li.classList.remove('active');
        submenu.classList.remove('active');
        if (arrow) {
            arrow.classList.remove('fa-chevron-up', 'bi-chevron-up');
            arrow.classList.add('fa-chevron-down', 'bi-chevron-down');
        }
    } else {
        // Đang đóng -> mở ra
        li.classList.add('active');
        submenu.classList.add('active');
        if (arrow) {
            arrow.classList.remove('fa-chevron-down', 'bi-chevron-down');
            arrow.classList.add('fa-chevron-up', 'bi-chevron-up');
        }
    }
}

function openMenu() {
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("overlay");
    sidebar.classList.add("active");
    overlay.classList.add("active");
    document.body.style.overflow = "hidden";
}

function closeMenu() {
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("overlay");
    sidebar.classList.remove("active");
    overlay.classList.remove("active");
    document.body.style.overflow = "auto";
}

// PRODUCT DETAIL UI

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

document.addEventListener('DOMContentLoaded', function () {
    updateCartCount();
});

// ========== TOAST NOTIFICATION SYSTEM ==========
const Toast = {
    // Hiển thị toast
    show: function(message, type = 'success', title = '') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        // Tạo phần tử toast
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        
        // Icon theo loại
        let iconHtml = '';
        switch(type) {
            case 'success':
                iconHtml = '<i class="fas fa-check-circle"></i>';
                title = title || 'Thành công!';
                break;
            case 'error':
                iconHtml = '<i class="fas fa-exclamation-circle"></i>';
                title = title || 'Lỗi!';
                break;
            case 'warning':
                iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
                title = title || 'Cảnh báo';
                break;
            case 'info':
                iconHtml = '<i class="fas fa-info-circle"></i>';
                title = title || 'Thông báo';
                break;
            default:
                iconHtml = '<i class="fas fa-bell"></i>';
        }

        toast.innerHTML = `
            <div class="toast-icon">${iconHtml}</div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <div class="toast-close">&times;</div>
        `;

        container.appendChild(toast);

        // Hiệu ứng hiện
        setTimeout(() => toast.classList.add('show'), 10);

        // Tự động ẩn sau 3 giây
        const timeout = setTimeout(() => this.hide(toast), 3000);

        // Nút đóng
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
            clearTimeout(timeout);
            this.hide(toast);
        });

        // Di chuột vào giữ, chuột ra tự ẩn
        toast.addEventListener('mouseenter', () => clearTimeout(timeout));
        toast.addEventListener('mouseleave', () => {
            const newTimeout = setTimeout(() => this.hide(toast), 1000);
            toast._timeout = newTimeout;
        });
    },

    hide: function(toast) {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    },

    // Các hàm tiện ích
    success: function(msg, title = '') {
        this.show(msg, 'success', title);
    },
    error: function(msg, title = '') {
        this.show(msg, 'error', title);
    },
    warning: function(msg, title = '') {
        this.show(msg, 'warning', title);
    },
    info: function(msg, title = '') {
        this.show(msg, 'info', title);
    }
};

window.Toast = Toast;