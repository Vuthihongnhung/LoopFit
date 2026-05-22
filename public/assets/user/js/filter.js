document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    if (!currentPath.includes('/products')) {
        return;
    }

    const productContainer = document.querySelector('.product-list');
    if (!productContainer) return;

    const genderChips = document.querySelectorAll('.filter-chips[data-filter="gender"] .chip');
    const priceChips = document.querySelectorAll('.filter-chips[data-filter="price"] .chip');
    const clearBtn = document.getElementById('clear-filters');
    const categoryInput = document.getElementById('filter-category');

    let currentGender = 'all';
    let currentPrice = 'all';
    let currentSort = 'default';
    let currentSearch = '';

    function formatNumber(num) {
        return num.toLocaleString('vi-VN');
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

    let BASE_URL = '';
    const baseMeta = document.querySelector('meta[name="base-url"]');
    if (baseMeta) {
        BASE_URL = baseMeta.getAttribute('content');
    } else {
        const path = window.location.pathname;
        if (path.includes('/LoopFit/public')) BASE_URL = '/LoopFit/public';
        else if (path.includes('/public')) BASE_URL = '/public';
        else BASE_URL = '';
    }

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
        let url = `${BASE_URL}/api/products?gender=${currentGender}&price=${currentPrice}&sort=${currentSort}`;
        if (category) url += `&category=${category}`;
        if (currentSearch) url += `&search=${encodeURIComponent(currentSearch)}`;

        fetch(url)
            .then(res => res.json())
            .then(products => {
                renderProductList(products);
                let newUrl = `${BASE_URL}/products?category=${category}&gender=${currentGender}&price=${currentPrice}&sort=${currentSort}`;
                if (currentSearch) newUrl += `&search=${encodeURIComponent(currentSearch)}`;
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
            let firstImage = (p.image || '').split(',')[0]?.trim() || 'default-product.png';
            let imgUrl = BASE_URL + '/uploads/' + firstImage;
            let price = parseFloat(p.price) || 0;
            let discountPercent = p.discount_percent || 0;
            let finalPrice = price;
            let oldPriceHtml = '';
            if (discountPercent > 0) {
                finalPrice = price * (100 - discountPercent) / 100;
                oldPriceHtml = `<span class="old-price">${formatNumber(price)}đ</span>`;
            }
            let formattedFinalPrice = formatNumber(finalPrice) + 'đ';
            let avgRating = Math.round((p.avg_rating || 0) * 10) / 10;
            let fullStars = Math.floor(avgRating);
            let halfStar = (avgRating - fullStars) >= 0.5;
            let starsHtml = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= fullStars) starsHtml += '<i class="fas fa-star"></i>';
                else if (halfStar && i === fullStars+1) starsHtml += '<i class="fas fa-star-half-alt"></i>';
                else starsHtml += '<i class="far fa-star"></i>';
            }
            let totalReviews = p.total_reviews || 0;
            let rentedCount = p.rented_count || 0;
            html += `
                <div class="product-card">
                    <a href="${BASE_URL}/product/${p.id}" class="product-link">
                        <div class="product-image">
                            <img src="${imgUrl}" alt="${escapeHtml(p.name)}">
                            ${discountPercent > 0 ? `<span class="discount-badge">-${discountPercent}%</span>` : ''}
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">${escapeHtml(p.name)}</h3>
                            <div class="product-rating">
                                <div class="stars">${starsHtml}</div>
                                <span class="rating-count">(${totalReviews})</span>
                                <span class="sold-count">Đã thuê ${formatNumber(rentedCount)}</span>
                            </div>
                            <div class="product-price">
                                <span class="current-price">${formattedFinalPrice}</span>
                                ${oldPriceHtml}
                                <span class="unit">/ngày</span>
                            </div>
                        </div>
                    </a>
                    <button class="quick-add" data-id="${p.id}"><i class="fas fa-cart-plus"></i> Thuê</button>
                </div>
            `;
        });
        productContainer.innerHTML = html;

        document.querySelectorAll('.quick-add').forEach(btn => {
            btn.removeEventListener('click', handleQuickAdd);
            btn.addEventListener('click', handleQuickAdd);
        });
    }

    function handleQuickAdd(e) {
        const btn = e.currentTarget;
        const productId = btn.getAttribute('data-id');
        alert('Thêm sản phẩm ' + productId + ' vào giỏ (tạm thời)');
    }

    // Gán sự kiện lọc
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
    let hasFilters = false;

    if (urlParams.has('gender')) {
        currentGender = urlParams.get('gender');
        setActive(genderChips, currentGender);
        hasFilters = true;
    }
    if (urlParams.has('price')) {
        currentPrice = urlParams.get('price');
        setActive(priceChips, currentPrice);
        hasFilters = true;
    }
    if (urlParams.has('sort')) {
        currentSort = urlParams.get('sort');
        hasFilters = true;
    }
    if (urlParams.has('search')) {
        currentSearch = urlParams.get('search');
        hasFilters = true;
    }
    if (urlParams.has('category')) {
        // Nếu có category, cập nhật categoryInput 
        const categoryId = urlParams.get('category');
        if (categoryInput) categoryInput.value = categoryId;
        hasFilters = true;
    }

    if (hasFilters) {
        fetchProducts();
    }
});