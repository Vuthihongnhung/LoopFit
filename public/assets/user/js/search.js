$(document).ready(function() {
    const searchInput = $('#search-input');
    const searchResults = $('#search-results');
    let searchTimeout = null;
    const delay = 300;

    let BASE_URL = '';
    const baseMeta = $('meta[name="base-url"]').attr('content');
    if (baseMeta) {
        BASE_URL = baseMeta;
    } else {
        const path = window.location.pathname;
        if (path.includes('/LoopFit/public')) BASE_URL = '/LoopFit/public';
        else if (path.includes('/public')) BASE_URL = '/public';
        else BASE_URL = '';
    }

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

    function renderDropdown(products) {
        if (!products.length) {
            searchResults.html('<div class="search-no-result">Không tìm thấy sản phẩm</div>').show();
            return;
        }
        let html = '<ul class="search-dropdown-list">';
        products.forEach(p => {
            let imgUrl = BASE_URL + '/uploads/' + (p.image ? p.image.split(',')[0].trim() : 'default-product.png');
            let price = parseFloat(p.price) || 0;
            let finalPrice = typeof p.final_price !== 'undefined' ? parseFloat(p.final_price) : price;
            if (isNaN(finalPrice) || finalPrice <= 0) {
                finalPrice = price;
            }
            let oldPriceHtml = '';
            if (finalPrice < price) {
                oldPriceHtml = `<span class="old-price">${formatNumber(price)}đ</span>`;
            }
            html += `
                <li>
                    <a href="${BASE_URL}/product/${p.id}">
                        <img src="${imgUrl}" alt="${escapeHtml(p.name)}">
                        <div class="search-info">
                            <div class="search-name">${escapeHtml(p.name)}</div>
                            <div class="search-price">
                                <span class="current-price">${formatNumber(finalPrice)}đ</span>
                                ${oldPriceHtml}
                                <span class="unit">/ngày</span>
                            </div>
                        </div>
                    </a>
                </li>
            `;
        });
        html += '</ul>';
        searchResults.html(html).show();
    }

    function hideDropdown() {
        searchResults.hide();
    }

    function performSearch(query) {
        if (query.length < 2) {
            hideDropdown();
            return;
        }
        $.ajax({
            url: BASE_URL + '/api/search',
            type: 'GET',
            data: { q: query },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    renderDropdown(res.data);
                } else {
                    searchResults.html('<div class="search-error">Lỗi tìm kiếm</div>').show();
                }
            },
            error: function() {
                searchResults.html('<div class="search-error">Lỗi kết nối</div>').show();
            }
        });
    }

    searchInput.on('input', function() {
        const query = $(this).val().trim();
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => performSearch(query), delay);
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-box, #search-results').length) {
            hideDropdown();
        }
    });

    searchInput.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const query = $(this).val().trim();
            if (query) {
                window.location.href = BASE_URL + '/products?search=' + encodeURIComponent(query);
            }
        }
    });
});