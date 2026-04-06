// filter.js (Phác thảo ý tưởng)
document.addEventListener('DOMContentLoaded', function() {
    const genderSelect = document.querySelector('select[name="gender"]');
    const priceSelect = document.querySelector('select[name="price"]');
    const cards = document.querySelectorAll('.product-card'); // Đảm bảo card có class này

    function filterProducts() {
        const selectedGender = genderSelect.value;
        const selectedPrice = priceSelect.value;

        cards.forEach(card => {
            // Lấy dữ liệu từ thuộc tính data- của card (bạn cần thêm vào HTML)
            const cardGender = card.getAttribute('data-gender'); 
            const cardPrice = parseFloat(card.getAttribute('data-price'));

            // Logic ẩn/hiện card bằng CSS display
            // ...
        });
    }

    // Nếu chọn cách này, bạn bỏ cái onchange="this.form.submit()" trong PHP đi
});