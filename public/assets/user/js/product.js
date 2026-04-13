window.addToCart = function (productId) {

    const qty = document.getElementById('qty')?.value;
    const rentDate = document.getElementById('rentDate')?.value;
    const returnDate = document.getElementById('returnDate')?.value;

    if (!selectedSize) return alert("Chọn size!");
    if (!rentDate || !returnDate) return alert("Chọn ngày!");

    fetch('/LoopFit/public/api/cart/add', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            product_id: productId,
            quantity: qty,
            size: selectedSize,
            rent_date: rentDate,
            return_date: returnDate
        })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return alert(data.message);

        alert("Đã thêm vào giỏ");
    });
};