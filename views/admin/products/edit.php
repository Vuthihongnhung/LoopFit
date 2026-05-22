<?php 
$pageTitle = 'Sửa sản phẩm'; 
$activeMenu = 'products';
$baseUrl = BASE_URL ?? '/LoopFit/public';
?>
<style>
/* ===== FORM SỬA SẢN PHẨM ===== */
.products-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 10px;
}
.products-page .card {
    background: linear-gradient(145deg, #fefce8 0%, #fef3c7 100%);
    border-radius: 24px;
    box-shadow: 0 25px 45px -12px rgba(245, 158, 11, 0.3);
    border: 1px solid rgba(245, 158, 11, 0.2);
    overflow: hidden;
}
.products-page .card-body {
    padding: 24px 28px;
    background: rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(2px);
}
.products-page .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}
.products-page .page-header h1 {
    font-size: 24px;
    font-weight: 800;
    background: linear-gradient(135deg, #92400e, #d97706, #f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0;
    letter-spacing: -1px;
}
.products-page .page-header h1::before {
    content: "✏️ ";
    background: none;
    -webkit-text-fill-color: #d97706;
}
.products-page .btn-secondary {
    background: linear-gradient(135deg, #d97706, #f59e0b);
    color: white;
    padding: 7px 18px;
    border-radius: 40px;
    border: none;
    text-decoration: none;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
}
.products-page .btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
}
.products-page .form-group {
    margin-bottom: 14px;
}
.products-page .form-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.products-page .form-row .form-group {
    flex: 1;
    min-width: 120px;
}
.products-page label {
    display: block;
    font-weight: 600;
    font-size: 12px;
    margin-bottom: 5px;
    color: #92400e;
}
.products-page label i {
    color: #f59e0b;
    width: 18px;
    margin-right: 6px;
    font-size: 12px;
}
.products-page input[type="text"],
.products-page input[type="number"],
.products-page select,
.products-page textarea {
    width: 100%;
    padding: 7px 12px;
    border: 1.5px solid #fde68a;
    border-radius: 16px;
    font-size: 13px;
    font-family: inherit;
    transition: all 0.3s ease;
    background: #fffbeb;
}
.products-page input:focus,
.products-page select:focus,
.products-page textarea:focus {
    outline: none;
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
    background: white;
}
.products-page .checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 4px 0;
}
.products-page .checkbox-group label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    font-size: 12px;
    padding: 5px 12px;
    border-radius: 30px;
    cursor: pointer;
    transition: all 0.25s ease;
    background: #fffbeb;
    border: 1.5px solid #fde68a;
}
/* ... (giữ nguyên các style màu sắc như cũ) ... */
.products-page .checkbox-group label:has(input[value="Trắng"]) { color: #6b7280; border-color: #d1d5db; }
.products-page .checkbox-group label:has(input[value="Đen"]) { color: #111827; border-color: #9ca3af; }
.products-page .checkbox-group label:has(input[value="Đỏ"]) { color: #dc2626; border-color: #fca5a5; }
.products-page .checkbox-group label:has(input[value="Xanh"]) { color: #2563eb; border-color: #93c5fd; }
.products-page .checkbox-group label:has(input[value="Vàng"]) { color: #ca8a04; border-color: #fde047; }
.products-page .checkbox-group label:has(input[value="Hồng"]) { color: #ec4899; border-color: #fbcfe8; }
.products-page .checkbox-group label:has(input[value="Tím"]) { color: #8b5cf6; border-color: #c4b5fd; }
.products-page .checkbox-group label:has(input[value="Xanh than"]) { color: #1e3a5f; border-color: #93c5fd; background: #eff6ff; }
.products-page .checkbox-group label:has(input[value="Xanh lục"]) { color: #10b981; border-color: #a7f3d0; background: #ecfdf5; }
.products-page .checkbox-group label:has(input[value="Nâu"]) { color: #78350f; border-color: #d4a574; background: #fef3c7; }
.products-page .checkbox-group label:has(input[value="Xám"]) { color: #6b7280; border-color: #d1d5db; background: #f9fafb; }
.products-page .checkbox-group label:has(input[value="Be"]) { color: #a8715a; border-color: #e5cdb5; background: #fdf8ed; }
.products-page .checkbox-group label:has(input[value="Cam"]) { color: #ea580c; border-color: #fdba74; background: #fff7ed; }
.products-page .checkbox-group label:has(input[value="Trắng"])::before { content: "●"; color: #9ca3af; margin-right: 5px; font-size: 11px; }
.products-page .checkbox-group label:has(input[value="Đen"])::before { content: "●"; color: #1f2937; margin-right: 5px; font-size: 11px; }
.products-page .checkbox-group label:has(input[value="Đỏ"])::before { content: "●"; color: #ef4444; margin-right: 5px; font-size: 11px; }
.products-page .checkbox-group label:has(input[value="Xanh"])::before { content: "●"; color: #3b82f6; margin-right: 5px; font-size: 11px; }
.products-page .checkbox-group label:has(input[value="Vàng"])::before { content: "●"; color: #eab308; margin-right: 5px; font-size: 11px; }
.products-page .checkbox-group label:has(input[value="Hồng"])::before { content: "●"; color: #ec4899; margin-right: 5px; font-size: 11px; }
.products-page .checkbox-group label:has(input[value="Tím"])::before { content: "●"; color: #a855f7; margin-right: 5px; font-size: 11px; }
.products-page .checkbox-group label:has(input[value="Xanh than"])::before { content: "●"; color: #1e3a5f; margin-right: 5px; font-size: 11px; }
.products-page .checkbox-group label:has(input[value="Xanh lục"])::before { content: "●"; color: #10b981; margin-right: 5px; font-size: 11px; }
.products-page .checkbox-group label:has(input[value="Nâu"])::before { content: "●"; color: #78350f; margin-right: 5px; font-size: 11px; }
.products-page .checkbox-group label:has(input[value="Xám"])::before { content: "●"; color: #6b7280; margin-right: 5px; font-size: 11px; }
.products-page .checkbox-group label:has(input[value="Be"])::before { content: "●"; color: #a8715a; margin-right: 5px; font-size: 11px; }
.products-page .checkbox-group label:has(input[value="Cam"])::before { content: "●"; color: #ea580c; margin-right: 5px; font-size: 11px; }
.products-page .checkbox-group label:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.products-page .checkbox-group input[type="checkbox"] {
    width: 14px;
    height: 14px;
    accent-color: #f59e0b;
    cursor: pointer;
}
.products-page input[type="file"] {
    padding: 6px 0;
    border: none;
}
.products-page input[type="file"]::file-selector-button {
    padding: 6px 16px;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border: none;
    border-radius: 30px;
    color: #92400e;
    font-weight: 600;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.25s;
}
.products-page input[type="file"]::file-selector-button:hover {
    background: linear-gradient(135deg, #fde68a, #fcd34d);
    transform: scale(1.02);
}
.products-page .form-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(245, 158, 11, 0.2);
    justify-content: flex-end;
}
.products-page .btn-primary {
    background: linear-gradient(135deg, #b45309, #d97706, #f59e0b);
    color: white;
    padding: 8px 24px;
    border-radius: 40px;
    border: none;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 6px 14px rgba(245, 158, 11, 0.35);
}
.products-page .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(245, 158, 11, 0.45);
}
.products-page #currentImagePreview img {
    border-radius: 12px;
    border: 2px solid #fde68a;
    padding: 3px;
    max-width: 70px;
    margin-top: 8px;
}
@media (max-width: 768px) {
    .products-page { padding: 8px; }
    .products-page .card-body { padding: 16px; }
    .products-page .form-row { flex-direction: column; gap: 0; }
    .products-page .page-header { flex-direction: column; text-align: center; }
    .products-page .page-header h1 { font-size: 20px; }
    .products-page .checkbox-group label { padding: 4px 10px; font-size: 11px; }
    .products-page .form-actions { flex-direction: column; }
    .products-page .btn-primary,
    .products-page .btn-secondary { justify-content: center; width: 100%; }
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
                        <input type="number" name="rented_count" id="productRentedCount" value="0">
                    </div>
                </div>

                <div class="form-group">
                    <label>Màu sắc </label>
                    <div class="checkbox-group" id="colorGroup">
                        <label><input type="checkbox" name="colors[]" value="Trắng"> Trắng</label>
                        <label><input type="checkbox" name="colors[]" value="Đen"> Đen</label>
                        <label><input type="checkbox" name="colors[]" value="Đỏ"> Đỏ</label>
                        <label><input type="checkbox" name="colors[]" value="Xanh"> Xanh</label>
                        <label><input type="checkbox" name="colors[]" value="Vàng"> Vàng</label>
                        <label><input type="checkbox" name="colors[]" value="Hồng"> Hồng</label>
                        <label><input type="checkbox" name="colors[]" value="Tím"> Tím</label>
                        <label><input type="checkbox" name="colors[]" value="Xanh than"> Xanh than</label>
                        <label><input type="checkbox" name="colors[]" value="Xanh lục"> Xanh lục</label>
                        <label><input type="checkbox" name="colors[]" value="Nâu"> Nâu</label>
                        <label><input type="checkbox" name="colors[]" value="Xám"> Xám</label>
                        <label><input type="checkbox" name="colors[]" value="Be"> Be</label>
                        <label><input type="checkbox" name="colors[]" value="Cam"> Cam</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Kích thước </label>
                    <div class="checkbox-group" id="sizeGroup">
                        <label><input type="checkbox" name="sizes[]" value="S"> S</label>
                        <label><input type="checkbox" name="sizes[]" value="M"> M</label>
                        <label><input type="checkbox" name="sizes[]" value="L"> L</label>
                        <label><input type="checkbox" name="sizes[]" value="XL"> XL</label>
                        <label><input type="checkbox" name="sizes[]" value="XXL"> XXL</label>
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
                // Màu sắc
                if (p.colors) {
                    const colorsArray = p.colors.split(',').map(c => c.trim());
                    document.querySelectorAll('#colorGroup input[type="checkbox"]').forEach(cb => {
                        cb.checked = colorsArray.includes(cb.value);
                    });
                }
                // Kích thước
                if (p.sizes) {
                    const sizesArray = p.sizes.split(',').map(s => s.trim());
                    document.querySelectorAll('#sizeGroup input[type="checkbox"]').forEach(cb => {
                        cb.checked = sizesArray.includes(cb.value);
                    });
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