function formatMoney(number) {
    return number.toLocaleString('vi-VN') + 'đ';
}

function updateCartTotal() {
    let total = 0;
    let totalDeposit = 0;
    document.querySelectorAll('.cart-item').forEach(item => {
        const checkbox = item.querySelector('.item-select');
        if (checkbox && checkbox.checked) {
            const price = parseFloat(item.dataset.price);
            const days = parseInt(item.dataset.days);
            const depositPrice = parseFloat(item.dataset.depositPrice);
            const cartId = item.dataset.cartId;
            const qtySpan = document.getElementById('qty-' + cartId);
            if (qtySpan) {
                const qty = parseInt(qtySpan.innerText);
                total += price * qty * days;
                totalDeposit += depositPrice * qty;
            }
        }
    });
    // Kiểm tra phần tử tồn tại trước khi gán
    const tempEl = document.getElementById('cart-total-temp');
    if (tempEl) tempEl.innerText = formatMoney(total);
    const depositEl = document.getElementById('cart-deposit-total');
    if (depositEl) depositEl.innerText = formatMoney(totalDeposit);
    const totalEl = document.getElementById('cart-total-all');
    if (totalEl) totalEl.innerText = formatMoney(total + totalDeposit);
}

window.updateQuantity = function(cartId, change) {
    fetch('/LoopFit/public/api/cart/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ cart_id: cartId, change: change })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (data.new_quantity !== undefined) {
                const qtySpan = document.getElementById('qty-' + cartId);
                qtySpan.innerText = data.new_quantity;
                const itemDiv = document.querySelector(`.cart-item[data-cart-id="${cartId}"]`);
                const price = parseFloat(itemDiv.dataset.price);
                const days = parseInt(itemDiv.dataset.days);
                const depositPrice = parseFloat(itemDiv.dataset.depositPrice);
                const totalSpan = document.getElementById('total-' + cartId);
                totalSpan.innerText = formatMoney(price * data.new_quantity * days);
                updateCartTotal();
            } else {
                location.reload();
            }
        } else {
            alert(data.message);
        }
    });
};

window.removeFromCart = function(cartId) {
    if (confirm("Xóa sản phẩm?")) {
        fetch('/LoopFit/public/api/cart/remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ cart_id: cartId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message);
        });
    }
};

function updateCartCount() {
    fetch('/LoopFit/public/api/cart/count')
        .then(res => res.json())
        .then(data => {
            const el = document.getElementById('cart-count');
            if (el) el.innerText = data.count;
        });
}

document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    updateCartTotal();

    // Sự kiện checkbox
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

    // Nút thanh toán
    const checkoutBtn = document.getElementById('checkout-selected');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            const selected = [];
            document.querySelectorAll('.item-select:checked').forEach(cb => {
                selected.push(cb.dataset.cartId);
            });
            if (selected.length === 0) {
                alert('Vui lòng chọn ít nhất một sản phẩm');
                return;
            }
            window.location.href = '/LoopFit/public/checkout?cart_ids=' + selected.join(',');
        });
    }
});