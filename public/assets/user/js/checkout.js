function calculateDays(start, end) {
    const s = new Date(start);
    const e = new Date(end);

    const diff = e - s;
    return Math.ceil(diff / (1000 * 60 * 60 * 24));
}

function updateTotal() {
    const start = document.querySelector('[name="rent_date"]').value;
    const end = document.querySelector('[name="return_date"]').value;

    if (!start || !end) return;

    const days = calculateDays(start, end);

    if (days <= 0) {
        alert("Ngày không hợp lệ");
        return;
    }

    document.getElementById('days').innerText = days;

    let total = 0;

    cart.forEach(item => {
        total += item.price * item.quantity * days;
    });

    document.getElementById('total-price').innerText =
        total.toLocaleString('vi-VN') + 'đ';
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[name="rent_date"], [name="return_date"]')
        .forEach(input => {
            input.addEventListener('change', updateTotal);
        });

    document.getElementById('checkout-form')
        .addEventListener('submit', function(e) {
            e.preventDefault();

            alert("Đặt thuê thành công! (demo)");
        });
});