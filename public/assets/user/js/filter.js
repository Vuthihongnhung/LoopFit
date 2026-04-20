document.addEventListener('DOMContentLoaded', function() {
    // Kiểm tra xem có đang ở trang danh sách sản phẩm không
    const productContainer = document.querySelector('.product-list');
    if (!productContainer) return; // Nếu không có, thoát (không chạy trên product detail)

    const genderChips = document.querySelectorAll('.filter-chips[data-filter="gender"] .chip');
    const priceChips = document.querySelectorAll('.filter-chips[data-filter="price"] .chip');
    const clearBtn = document.getElementById('clear-filters');
    const categoryInput = document.getElementById('filter-category');

    let currentGender = 'all';
    let currentPrice = 'all';

    function setActive(chips, value) {
        chips.forEach(chip => {
            if (chip.getAttribute('data-value') === value) {
                chip.classList.add('active');
            } else {
                chip.classList.remove('active');
            }
        });
    }

    function fetchProducts() {
        let category = categoryInput ? categoryInput.value : '';
        let url = `/LoopFit/public/api/products?gender=${currentGender}&price=${currentPrice}`;
        if (category) url += `&category=${category}`;

        fetch(url)
            .then(res => res.json())
            .then(products => {
                renderProductList(products);
                let newUrl = `/LoopFit/public/products?category=${category}&gender=${currentGender}&price=${currentPrice}`;
                window.history.replaceState({}, '', newUrl);
            })
            .catch(err => console.error('Lỗi fetch:', err));
    }

    function renderProductList(products) {
        if (!productContainer) return;
        if (!products.length) {
            productContainer.innerHTML = '<p>Không có sản phẩm nào phù hợp.</p>';
            return;
        }
        let html = '';
        products.forEach(p => {
            let imagesStr = p.image || '';
            let firstImage = imagesStr.split(',')[0]?.trim() || 'no-image.png';
            let imgUrl = '/LoopFit/public/uploads/' + firstImage;
            let fallbackSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23f5f5f5'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23999' font-family='Arial' font-size='14'%3ENo Image%3C/text%3E%3C/svg%3E";
            html += `
                <div class="product-card">
                    <a href="/LoopFit/public/product/${p.id}" class="product-link">
                        <div class="product-image">
                            <img src="${imgUrl}" alt="${escapeHtml(p.name)}" onerror="this.onerror=null; this.src='${fallbackSvg}';">
                        </div>
                        <div class="product-info">
                            <h3>${escapeHtml(p.name)}</h3>
                            <p class="price">${Number(p.price).toLocaleString()}đ <span class="unit">/ ngày</span></p>
                        </div>
                    </a>
                    
                </div>
            `;
        });
        productContainer.innerHTML = html;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    genderChips.forEach(chip => {
        chip.addEventListener('click', function() {
            currentGender = this.getAttribute('data-value');
            setActive(genderChips, currentGender);
            fetchProducts();
        });
    });
    priceChips.forEach(chip => {
        chip.addEventListener('click', function() {
            currentPrice = this.getAttribute('data-value');
            setActive(priceChips, currentPrice);
            fetchProducts();
        });
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            currentGender = 'all';
            currentPrice = 'all';
            setActive(genderChips, 'all');
            setActive(priceChips, 'all');
            fetchProducts();
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('gender')) {
        currentGender = urlParams.get('gender');
        setActive(genderChips, currentGender);
    }
    if (urlParams.has('price')) {
        currentPrice = urlParams.get('price');
        setActive(priceChips, currentPrice);
    }

    fetchProducts();
});