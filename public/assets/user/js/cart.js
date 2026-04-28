// ========== ĐỊNH DẠNG TIỀN ==========
function formatMoney(number) {
    return number.toLocaleString('vi-VN') + 'đ';
}

// ========== TÍNH TỔNG TIỀN (ĐÃ SỬA LOGIC CỌC) ==========
function updateCartTotal() {
    let totalRent = 0;
    let totalDeposit = 0;

    document.querySelectorAll('.cart-item').forEach(item => {
        const checkbox = item.querySelector('.item-select');
        if (checkbox && checkbox.checked) {
            let price = parseFloat(item.dataset.price);
            let days = parseInt(item.dataset.days, 10);
            let depositPerItem = parseFloat(item.dataset.depositPrice);
            let cartId = item.dataset.cartId;
            const qtySpan = document.getElementById('qty-' + cartId);
            let qty = qtySpan ? parseInt(qtySpan.innerText, 10) : 1;

            if (isNaN(price)) price = 0;
            if (isNaN(days)) days = 0;
            if (isNaN(depositPerItem)) depositPerItem = 0;
            if (isNaN(qty)) qty = 1;

            totalRent += price * days * qty;
            totalDeposit += depositPerItem * qty;  // cọc = cọc 1sp * số lượng
        }
    });

    const grandTotal = totalRent + totalDeposit;

    document.getElementById('cart-total-temp') && (document.getElementById('cart-total-temp').innerText = formatMoney(totalRent));
    document.getElementById('cart-deposit-total') && (document.getElementById('cart-deposit-total').innerText = formatMoney(totalDeposit));
    document.getElementById('cart-total-all') && (document.getElementById('cart-total-all').innerText = formatMoney(grandTotal));
}

// ========== CẬP NHẬT BADGE GIỎ HÀNG ==========
function updateCartCount() {
    fetch('/LoopFit/public/api/cart/count')
        .then(res => res.json())
        .then(data => {
            const el = document.getElementById('cart-count');
            if (el) el.innerText = data.count;
        })
        .catch(() => {});
}

// ========== MODAL XÁC NHẬN XÓA ==========
let confirmModal = null;
function initConfirmModal() { /* ... giữ nguyên như cũ ... */ }
function showConfirm(message, onConfirm) { /* ... */ }

// ========== CẬP NHẬT SỐ LƯỢNG (OPTIMISTIC + BADGE) ==========
window.updateQuantity = function(cartId, change) {
    const qtySpan = document.getElementById('qty-' + cartId);
    if (!qtySpan) return;
    let currentQty = parseInt(qtySpan.innerText, 10);
    let newQty = currentQty + change;
    if (newQty < 1) return;

    const oldQty = currentQty;
    qtySpan.innerText = newQty;

    const itemDiv = document.querySelector(`.cart-item[data-cart-id="${cartId}"]`);
    if (itemDiv) {
        const price = parseFloat(itemDiv.dataset.price);
        const days = parseInt(itemDiv.dataset.days, 10);
        const totalSpan = document.getElementById('total-' + cartId);
        if (totalSpan) totalSpan.innerText = formatMoney(price * newQty * days);
    }
    updateCartTotal();

    fetch('/LoopFit/public/api/cart/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ cart_id: cartId, change: change })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            // rollback
            qtySpan.innerText = oldQty;
            if (itemDiv) {
                const price = parseFloat(itemDiv.dataset.price);
                const days = parseInt(itemDiv.dataset.days, 10);
                const totalSpan = document.getElementById('total-' + cartId);
                if (totalSpan) totalSpan.innerText = formatMoney(price * oldQty * days);
            }
            updateCartTotal();
            Toast.error(data.message || 'Không thể cập nhật số lượng');
        } else {
            // Cập nhật badge ngay lập tức
            updateCartCount();
        }
    })
    .catch(() => {
        // rollback lỗi mạng
        qtySpan.innerText = oldQty;
        if (itemDiv) {
            const price = parseFloat(itemDiv.dataset.price);
            const days = parseInt(itemDiv.dataset.days, 10);
            const totalSpan = document.getElementById('total-' + cartId);
            if (totalSpan) totalSpan.innerText = formatMoney(price * oldQty * days);
        }
        updateCartTotal();
        Toast.error('Lỗi kết nối, vui lòng thử lại');
    });
};

// ========== XÓA SẢN PHẨM (KHÔNG RELOAD) ==========
window.removeFromCart = function(cartId) {
    showConfirm('Bạn có chắc chắn muốn xóa sản phẩm này?', () => {
        fetch('/LoopFit/public/api/cart/remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ cart_id: cartId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const itemDiv = document.querySelector(`.cart-item[data-cart-id="${cartId}"]`);
                if (itemDiv) itemDiv.remove();
                updateCartTotal();
                updateCartCount();  // cập nhật badge
                Toast.success('Đã xóa sản phẩm');
                if (document.querySelectorAll('.cart-item').length === 0) {
                    location.reload(); // hoặc hiển thị giỏ trống
                }
            } else {
                Toast.error(data.message || 'Xóa thất bại');
            }
        })
        .catch(() => Toast.error('Lỗi kết nối'));
    });
};

// ========== KHỞI TẠO ==========
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    updateCartTotal();
    initConfirmModal();

    document.querySelectorAll('.item-select').forEach(cb => {
        cb.addEventListener('change', updateCartTotal);
    });

    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.item-select').forEach(cb => cb.checked = this.checked);
            updateCartTotal();
        });
    }

    const checkoutBtn = document.getElementById('checkout-selected');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            const selected = [];
            document.querySelectorAll('.item-select:checked').forEach(cb => {
                selected.push(cb.dataset.cartId);
            });
            if (selected.length === 0) {
                Toast.warning('Vui lòng chọn sản phẩm');
                return;
            }
            window.location.href = '/LoopFit/public/checkout?cart_ids=' + selected.join(',');
        });
    }
});