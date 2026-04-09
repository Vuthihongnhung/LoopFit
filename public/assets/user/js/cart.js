// format tiền VND
function formatMoney(number) {
    return number.toLocaleString('vi-VN') + 'đ';
}

// cập nhật tổng toàn giỏ
function updateCartTotal() {
    let total = 0;

    document.querySelectorAll('.cart-item').forEach(item => {
        const price = parseInt(item.dataset.price);
        const id = item.id.split('-')[2];
        const qty = parseInt(document.getElementById('qty-' + id).innerText);

        total += price * qty;
    });

    const totalEl = document.getElementById('cart-total');
    if (totalEl) {
        totalEl.innerText = formatMoney(total);
    }
}

// tăng / giảm số lượng
function updateQuantity(productId, change) {
    const qtyEl = document.getElementById('qty-' + productId);
    const itemEl = document.getElementById('cart-item-' + productId);

    if (!qtyEl || !itemEl) return;

    let qty = parseInt(qtyEl.innerText);
    qty += change;

    if (qty < 1) return;

    qtyEl.innerText = qty;

    // update total từng item
    const price = parseInt(itemEl.dataset.price);
    const totalEl = document.getElementById('total-' + productId);

    if (totalEl) {
        totalEl.innerText = formatMoney(price * qty);
    }

    updateCartTotal();
}

// xóa sản phẩm
function removeFromCart(productId) {
    if (!confirm("Xóa sản phẩm?")) return;

    const item = document.getElementById('cart-item-' + productId);
    if (item) {
        item.remove();
    }

    updateCartTotal();
}

// chạy khi load trang
document.addEventListener('DOMContentLoaded', function () {
    updateCartTotal();
});

function updateCartCount(count) {
    const el = document.getElementById('cart-count');
    if (!el) return;

    if (count <= 0) {
        el.style.display = 'none';
    } else {
        el.style.display = 'inline-block';
        el.innerText = count;
    }
}

// 🔥 test fake
document.addEventListener('DOMContentLoaded', function () {
    updateCartCount(3); // đổi số ở đây để test
});