function formatMoney(number) {
    return number.toLocaleString('vi-VN') + 'đ';
}

function updateCartTotal() {
    let total = 0;

    document.querySelectorAll('.cart-item').forEach(item => {
        const price = parseInt(item.dataset.price);
        const cartId = item.id.replace('cart-item-', '');

        const qtyEl = document.getElementById('qty-' + cartId);
        if (!qtyEl) return;

        total += price * parseInt(qtyEl.innerText);
    });

    const el = document.getElementById('cart-total');
    if (el) el.innerText = formatMoney(total);
}

window.updateQuantity = function (cartId, change) {
    const qtyEl = document.getElementById('qty-' + cartId);
    if (!qtyEl) return;

    let current = parseInt(qtyEl.innerText);
    let newQty = current + change;

    if (newQty < 1) return;

    qtyEl.innerText = newQty;

    const item = document.getElementById('cart-item-' + cartId);
    const price = parseInt(item.dataset.price);

    const totalEl = document.getElementById('total-' + cartId);
    if (totalEl) {
        totalEl.innerText = formatMoney(price * newQty);
    }

    updateCartTotal();
};

window.removeFromCart = function (cartId) {
    if (!confirm("Xóa sản phẩm?")) return;

    fetch('/LoopFit/public/api/cart/remove', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({cart_id: cartId})
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return alert(data.message);

        document.getElementById('cart-item-' + cartId)?.remove();
        updateCartTotal();
    });
};