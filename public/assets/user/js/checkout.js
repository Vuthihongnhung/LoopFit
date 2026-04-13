// =====================
// GLOBAL STATE
// =====================
let currentDiscountPercent = 0;
let currentDiscountAmount = 0;
let maxDiscount = 0;

// =====================
// FORMAT TIỀN
// =====================
function formatMoney(number) {
    return number.toLocaleString('vi-VN') + 'đ';
}

// =====================
// LẤY CART (từ backend render ra window.cart)
// =====================
function getCart() {
    return window.cart || [];
}

// =====================
// TÍNH TỔNG TIỀN (DÙNG item_total từ backend)
// =====================
function updateTotal() {
    let total = 0;

    const cart = getCart();

    if (!cart || cart.length === 0) {
        return;
    }

    cart.forEach(item => {
        const price = Number(item.price) || 0;
        const qty = Number(item.quantity) || 0;

        // ưu tiên days từ backend
        let days = Number(item.days);

        // fallback nếu thiếu days
        if (!days || days <= 0) {
            if (item.rent_date && item.return_date) {
                const start = new Date(item.rent_date);
                const end = new Date(item.return_date);
                days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
            } else {
                days = 1;
            }
        }

        total += price * qty * days;
    });

    window.baseTotal = total;

    applyDiscount();
}

// =====================
// ÁP DỤNG GIẢM GIÁ
// =====================
function applyDiscount() {
    let total = window.baseTotal || 0;
    let discount = 0;

    // % discount
    if (currentDiscountPercent > 0) {
        discount = Math.floor(total * currentDiscountPercent / 100);

        // giới hạn max
        if (maxDiscount > 0 && discount > maxDiscount) {
            discount = maxDiscount;
        }
    }
    // fixed discount
    else if (currentDiscountAmount > 0) {
        discount = currentDiscountAmount;
    }

    // không cho âm
    if (discount > total) discount = total;

    let final = total - discount;

    // =====================
    // UPDATE UI
    // =====================
    const totalEl = document.getElementById('cart-total');
    const discountEl = document.getElementById('discount-amount');
    const finalEl = document.getElementById('final-total');

    if (totalEl) totalEl.innerText = formatMoney(total);
    if (discountEl) discountEl.innerText = '-' + formatMoney(discount);
    if (finalEl) finalEl.innerText = formatMoney(final);
}

// =====================
// INIT
// =====================
document.addEventListener("DOMContentLoaded", function () {

    const promoSelect = document.getElementById("promo-select");

    if (!promoSelect) {
        console.log("❌ Không tìm thấy promo-select");
        return;
    }

    promoSelect.addEventListener("change", function () {

        const selected = this.options[this.selectedIndex];
        const percent = parseInt(selected.getAttribute("data-percent")) || 0;

        const totalEl = document.getElementById("cart-total");
        const discountEl = document.getElementById("discount-amount");
        const finalEl = document.getElementById("final-total");

        if (!totalEl) {
            console.log("❌ Không tìm thấy cart-total");
            return;
        }

        let total = parseInt(totalEl.innerText.replace(/\D/g, '')) || 0;

        let discount = total * percent / 100;
        let final = total - discount;

        discountEl.innerText = discount.toLocaleString() + "đ";
        finalEl.innerText = final.toLocaleString() + "đ";
    });

});