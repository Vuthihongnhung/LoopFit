<?php 
$pageTitle = 'Sửa sản phẩm'; 
$activeMenu = 'products';
$baseUrl = BASE_URL ?? '/LoopFit/public';
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap');

.products-page {
    max-width: 760px;
    margin: 0 auto;
    padding: 12px;
    font-family: 'Montserrat', sans-serif;
    color: #24344f;
    background: #F0F0ED;
}

.products-page .card {
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 20px 45px rgba(33, 54, 94, 0.12);
    border: 1px solid rgba(113, 139, 188, 0.16);
    overflow: hidden;
}

.products-page .card-body {
    padding: 24px 28px;
}

.products-page .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
    flex-wrap: wrap;
    gap: 14px;
}

.products-page .page-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 700;
    color: #718BBC;
    margin: 0;
    letter-spacing: -0.4px;
}

.products-page .page-header h1::before {
    content: "✏️ ";
    color: #718BBC;
}

.products-page .btn-secondary {
    background: #ffffff;
    color: #718BBC;
    padding: 10px 22px;
    border-radius: 999px;
    border: 1px solid #718BBC;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.products-page .btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(113, 137, 188, 0.18);
}

.products-page .variant-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.products-page .variant-actions .btn-secondary {
    padding: 8px 14px;
    min-width: 130px;
}

.products-page .variant-actions small {
    margin: 0;
    color: #475569;
    line-height: 1.4;
}

.products-page .form-group {
    margin-bottom: 14px;
}

.products-page .form-row {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}

.products-page .form-row .form-group {
    flex: 1;
    min-width: 160px;
}

.products-page label {
    display: block;
    font-weight: 600;
    font-size: 12px;
    margin-bottom: 6px;
    color: #35415d;
}

.products-page input[type="text"],
.products-page input[type="number"],
.products-page select,
.products-page textarea {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #d7dbe5;
    border-radius: 16px;
    font-size: 13px;
    font-family: 'Montserrat', sans-serif;
    transition: border-color 0.25s ease, box-shadow 0.25s ease;
    background: #f7f8fb;
}

.products-page input:focus,
.products-page select:focus,
.products-page textarea:focus {
    outline: none;
    border-color: #718BBC;
    box-shadow: 0 0 0 4px rgba(113, 139, 188, 0.12);
    background: #ffffff;
}

.products-page .checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 4px 0;
}

.products-page .checkbox-group label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    font-size: 13px;
    padding: 10px 16px;
    border-radius: 999px;
    cursor: pointer;
    transition: all 0.25s ease;
    background: #f7f8fb;
    border: 1px solid #e4e7ef;
}

.products-page .checkbox-group label:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(113, 139, 188, 0.12);
}

.products-page .checkbox-group input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: #718BBC;
    cursor: pointer;
    margin: 0;
}

.products-page input[type="file"] {
    padding: 8px 0;
    border: none;
}

.products-page input[type="file"]::file-selector-button {
    padding: 10px 18px;
    background: #718BBC;
    border: none;
    border-radius: 999px;
    color: white;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.25s ease, transform 0.25s ease;
}

.products-page input[type="file"]::file-selector-button:hover {
    background: #526b96;
    transform: scale(1.02);
}

.products-page .form-actions {
    display: flex;
    gap: 14px;
    margin-top: 26px;
    padding-top: 18px;
    border-top: 1px solid rgba(113, 139, 188, 0.18);
    justify-content: flex-end;
}

.products-page .btn-primary {
    background: linear-gradient(135deg, #4f6fa8, #718BBC);
    color: white;
    padding: 10px 24px;
    border-radius: 999px;
    border: none;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    box-shadow: 0 10px 24px rgba(113, 139, 188, 0.25);
}

.products-page .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(113, 139, 188, 0.3);
}

.products-page #currentImagePreview img {
    border-radius: 14px;
    border: 1px solid #e3e7f0;
    padding: 4px;
    max-width: 100px;
    margin-top: 10px;
}

.products-page .table-responsive {
    overflow-x: auto;
}

.products-page .data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
}

.products-page .data-table th,
.products-page .data-table td {
    padding: 14px 12px;
    border-bottom: 1px solid #e8ebf3;
    text-align: left;
}

.products-page .data-table th {
    background: #f0f2f7;
    color: #4b5d7b;
    font-weight: 600;
}

.products-page .remove-variant-btn {
    background: #fff1f0;
    color: #b34040;
    border: 1px solid #f0c2c1;
    border-radius: 999px;
    padding: 8px 14px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.products-page .remove-variant-btn:hover {
    background: #ffd9d7;
}

@media (max-width: 768px) {
    .products-page {
        padding: 14px;
    }
    .products-page .card-body {
        padding: 24px;
    }
    .products-page .form-row {
        flex-direction: column;
    }
    .products-page .page-header {
        text-align: center;
    }
    .products-page .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    .products-page .btn-primary,
    .products-page .btn-secondary {
        width: 100%;
        justify-content: center;
    }
}
</style>
<script>
    // Sử dụng BASE_URL toàn cục (đã được định nghĩa trong layout admin)
    if (typeof BASE_URL === 'undefined') {
        var BASE_URL = '<?= $baseUrl ?>';
    }
</script>

<div class="products-page">
    <div class="page-header">
        <h1><i class="fas fa-edit"></i> Sửa sản phẩm</h1>
        <a href="<?= BASE_URL ?>/admin/products" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
    <div class="card">
        <div class="card-body">
            <form id="editProductForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="productId">
                <div class="form-group">
                    <label>Tên sản phẩm</label>
                    <input type="text" name="name" id="productName" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Giá (VNĐ)</label>
                        <input type="number" name="price" id="productPrice" required>
                    </div>
                    <div class="form-group">
                        <label>Giảm giá (%)</label>
                        <input type="number" name="discount_percent" id="productDiscountPercent" value="0">
                    </div>
                    <div class="form-group">
                        <label>Giá cọc (VNĐ)</label>
                        <input type="number" name="deposit_price" id="productDepositPrice" required>
                    </div>
                    <div class="form-group">
                        <label>Tồn kho</label>
                        <input type="number" name="stock" id="productStock" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Nhóm danh mục</label>
                    <select id="parentCat" class="form-control" required>
                        <option value="">-- Chọn nhóm --</option>
                        <?php if (!empty($parentCategories)): ?>
                            <?php foreach ($parentCategories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>Chưa có danh mục cha</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Danh mục con</label>
                    <select name="category_id" id="childCat" class="form-control" required>
                        <option value="">-- Chọn danh mục --</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Giới tính</label>
                        <select name="gender" id="productGender" class="form-control">
                            <option value="unisex">Unisex</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Số lần thuê</label>
                        <input type="number" id="productRentedCount" value="0" readonly class="form-control" style="background:#f8f9fa; cursor:not-allowed;">
                    </div>
                </div>

                <div class="form-group">
                    <label>Biến thể sản phẩm</label>
                    <div class="variant-actions" style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                        <button type="button" id="addVariantBtn" class="btn btn-secondary" style="padding: 8px 12px;">Thêm biến thể</button>
                        <small style="color:#475569;">Màu sắc - Kích thước - Tồn kho. Nếu có biến thể, tổng tồn kho sẽ được cập nhật tự động.</small>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table" id="variantTable" style="min-width: 100%;">
                            <thead>
                                <tr>
                                    <th>Màu sắc</th>
                                    <th>Kích thước</th>
                                    <th>Tồn kho</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" id="productDescription" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Ảnh hiện tại</label>
                    <div id="currentImagePreview"></div>
                </div>
                <div class="form-group">
                    <label>Thay đổi ảnh (chọn nhiều ảnh mới sẽ thay thế ảnh cũ)</label>
                    <input type="file" name="images[]" multiple accept="image/*" class="form-control">
                    <small>Nếu không chọn ảnh mới, ảnh cũ giữ nguyên</small>
                </div>
                <input type="hidden" name="old_images" id="oldImages" value="">
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cập nhật</button>
                    <a href="<?= BASE_URL ?>/admin/products" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Đợi DOM load
document.addEventListener('DOMContentLoaded', function() {
    const parentSelect = document.getElementById('parentCat');
    const childSelect = document.getElementById('childCat');
    let categoriesCache = {};

    async function loadChildren(parentId) {
        childSelect.innerHTML = '<option value="">-- Đang tải --</option>';
        if (!parentId) {
            childSelect.innerHTML = '<option value="">-- Chọn danh mục --</option>';
            return;
        }
        if (categoriesCache[parentId]) {
            renderChildOptions(categoriesCache[parentId]);
            return;
        }
        try {
            const response = await fetch(`${BASE_URL}/admin/api/categories?parent_id=${parentId}`);
            const data = await response.json();
            if (data.success && data.categories && data.categories.length) {
                categoriesCache[parentId] = data.categories;
                renderChildOptions(data.categories);
            } else {
                childSelect.innerHTML = '<option value="">Không có danh mục con</option>';
            }
        } catch (err) {
            console.error('Lỗi tải danh mục con:', err);
            childSelect.innerHTML = '<option value="">Lỗi tải danh mục</option>';
        }
    }

    function renderChildOptions(categories) {
        childSelect.innerHTML = '<option value="">-- Chọn danh mục --</option>';
        categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            childSelect.appendChild(option);
        });
    }

    parentSelect.addEventListener('change', () => {
        loadChildren(parentSelect.value);
    });

    const variantTableBody = document.querySelector('#variantTable tbody');
    const addVariantBtn = document.getElementById('addVariantBtn');

    function addVariantRow(variant = { color: '', size: '', stock: 0 }) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select name="variant_color[]" required>
                    <option value="">Chọn màu</option>
                    <option value="Trắng">Trắng</option>
                    <option value="Đen">Đen</option>
                    <option value="Đỏ">Đỏ</option>
                    <option value="Xanh">Xanh</option>
                    <option value="Vàng">Vàng</option>
                    <option value="Hồng">Hồng</option>
                    <option value="Tím">Tím</option>
                    <option value="Xanh than">Xanh than</option>
                    <option value="Xanh lục">Xanh lục</option>
                    <option value="Nâu">Nâu</option>
                    <option value="Xám">Xám</option>
                    <option value="Be">Be</option>
                    <option value="Cam">Cam</option>
                </select>
            </td>
            <td>
                <select name="variant_size[]" required>
                    <option value="">Chọn size</option>
                    <option value="S">S</option>
                    <option value="M">M</option>
                    <option value="L">L</option>
                    <option value="XL">XL</option>
                    <option value="XXL">XXL</option>
                </select>
            </td>
            <td>
                <input type="number" name="variant_stock[]" min="0" value="${variant.stock}" required>
            </td>
            <td>
                <button type="button" class="remove-variant-btn">Xóa</button>
            </td>
        `;
        variantTableBody.appendChild(row);

        const colorSelect = row.querySelector('select[name="variant_color[]"]');
        const sizeSelect = row.querySelector('select[name="variant_size[]"]');
        colorSelect.value = variant.color;
        sizeSelect.value = variant.size;

        colorSelect.addEventListener('mousedown', (e) => e.stopPropagation());
        sizeSelect.addEventListener('mousedown', (e) => e.stopPropagation());

        const stockInput = row.querySelector('input[name="variant_stock[]"]');
        stockInput.addEventListener('input', updateProductStockFromVariants);

        const removeBtn = row.querySelector('.remove-variant-btn');
        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            row.remove();
            updateProductStockFromVariants();
        });

        updateProductStockFromVariants();
    }

    addVariantBtn.addEventListener('click', () => addVariantRow());

    function updateProductStockFromVariants() {
        const stockInput = document.getElementById('productStock');
        const variantStockInputs = variantTableBody.querySelectorAll('input[name="variant_stock[]"]');
        const total = Array.from(variantStockInputs).reduce((sum, input) => {
            const value = parseInt(input.value, 10);
            return sum + (Number.isFinite(value) ? value : 0);
        }, 0);

        if (variantStockInputs.length > 0) {
            stockInput.value = total;
            stockInput.readOnly = true;
        } else {
            stockInput.readOnly = false;
        }
    }

    if (!variantTableBody.hasChildNodes()) {
        addVariantRow();
    }

    // Lấy thông tin sản phẩm
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    if (productId) {
        fetch(`${BASE_URL}/admin/api/product?id=${productId}`)
            .then(res => res.json())
            .then(async data => {
                const p = data.data;
                if (!p) return;

                document.getElementById('productId').value = p.id;
                document.getElementById('productName').value = p.name || '';
                document.getElementById('productPrice').value = p.price || 0;
                document.getElementById('productDiscountPercent').value = p.discount_percent || 0;
                document.getElementById('productDepositPrice').value = p.deposit_price || 0;
                document.getElementById('productStock').value = p.stock || 0;
                document.getElementById('productDescription').value = p.description || '';
                document.getElementById('productGender').value = p.gender || 'unisex';
                document.getElementById('productRentedCount').value = p.rented_count || 0;

                // Hiển thị ảnh hiện tại
                if (p.image) {
                    document.getElementById('oldImages').value = p.image;
                    const imageUrls = p.image.split(',');
                    let html = '';
                    imageUrls.forEach(img => {
                        img = img.trim();
                        if (img) {
                            let imgUrl = img;
                            if (!img.startsWith('http') && !img.startsWith('/')) {
                                imgUrl = `${BASE_URL}/uploads/${img}`;
                            } else if (img.startsWith('/uploads/')) {
                                imgUrl = BASE_URL + img;
                            }
                            html += `<img src="${imgUrl}" width="80" style="margin:5px; border-radius:5px;">`;
                        }
                    });
                    document.getElementById('currentImagePreview').innerHTML = html;
                }
                // Biến thể sản phẩm
                if (p.variants && p.variants.length) {
                    document.querySelector('#variantTable tbody').innerHTML = '';
                    p.variants.forEach(v => {
                        addVariantRow({ color: v.color, size: v.size, stock: v.stock });
                    });
                } else {
                    document.querySelector('#variantTable tbody').innerHTML = '';
                }

                // Xử lý danh mục cha/con
                if (p.category_id) {
                    try {
                        const catResponse = await fetch(`${BASE_URL}/admin/api/category?id=${p.category_id}`);
                        const catData = await catResponse.json();
                        if (catData.success && catData.category) {
                            const parentId = catData.category.parent_id;
                            if (parentId == 0) {
                                parentSelect.value = p.category_id;
                                childSelect.innerHTML = '<option value="">-- Chọn danh mục --</option>';
                            } else {
                                parentSelect.value = parentId;
                                await loadChildren(parentId);
                                childSelect.value = p.category_id;
                            }
                        } else {
                            console.warn('Không tìm thấy category:', p.category_id);
                        }
                    } catch (err) {
                        console.error('Lỗi khi lấy category:', err);
                    }
                }
            })
            .catch(error => {
                console.error('Lỗi fetch sản phẩm:', error);
                alert('Lỗi tải dữ liệu sản phẩm');
            });
    }

    // Submit form
    const form = document.getElementById('editProductForm');
    form.onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        try {
            const response = await fetch(`${BASE_URL}/admin/products/update`, { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                alert('Cập nhật thành công');
                window.location.href = `${BASE_URL}/admin/products`;
            } else {
                alert(data.error || 'Lỗi khi cập nhật');
            }
        } catch (err) {
            alert('Lỗi kết nối: ' + err.message);
        }
    };
});
</script>