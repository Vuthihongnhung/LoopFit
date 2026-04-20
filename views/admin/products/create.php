<?php 
$pageTitle = 'Thêm sản phẩm'; $activeMenu = 'products'; ?>
<style>
/* ===== FORM THÊM & SỬA SẢN PHẨM ===== */
.products-page {
    max-width: 950px;
    margin: 0 auto;
    padding: 20px;
}

.products-page .card {
    background: linear-gradient(145deg, #eef2ff 0%, #d9e6f5 100%);
    border-radius: 32px;
    box-shadow: 0 25px 45px -12px rgba(37, 99, 235, 0.3);
    border: 1px solid rgba(37, 99, 235, 0.2);
    overflow: hidden;
}

.products-page .card-body {
    padding: 45px 40px;
    background: rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(2px);
}

.products-page .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
    flex-wrap: wrap;
    gap: 15px;
}

.products-page .page-header h1 {
    font-size: 36px;
    font-weight: 800;
    background: linear-gradient(135deg, #0f2b6d, #1e40af, #2563eb);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0;
    letter-spacing: -1px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.05);
}

.products-page .btn-secondary {
    background: linear-gradient(135deg, #1e40af, #2563eb);
    color: white;
    padding: 10px 24px;
    border-radius: 40px;
    border: none;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
}

.products-page .btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
    background: linear-gradient(135deg, #2563eb, #3b82f6);
}

.products-page .form-group {
    margin-bottom: 24px;
}

.products-page .form-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.products-page .form-row .form-group {
    flex: 1;
    min-width: 150px;
}

.products-page label {
    display: block;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 8px;
    color: #0f2b6d;
}

.products-page label i {
    color: #2563eb;
    width: 20px;
    margin-right: 8px;
}

.products-page input[type="text"],
.products-page input[type="number"],
.products-page select,
.products-page textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid #cbd5e1;
    border-radius: 20px;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.3s ease;
    background: white;
}

.products-page input:focus,
.products-page select:focus,
.products-page textarea:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}

.products-page .checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    padding: 8px 0;
}

.products-page .checkbox-group label {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    font-size: 14px;
    padding: 8px 18px;
    border-radius: 40px;
    cursor: pointer;
    transition: all 0.25s ease;
    background: white;
    border: 1.5px solid #e2e8f0;
}

/* Màu sắc cho từng checkbox */
.products-page .checkbox-group label:has(input[value="Trắng"]) { color: #6b7280; border-color: #d1d5db; }
.products-page .checkbox-group label:has(input[value="Đen"]) { color: #111827; border-color: #9ca3af; }
.products-page .checkbox-group label:has(input[value="Đỏ"]) { color: #dc2626; border-color: #fca5a5; }
.products-page .checkbox-group label:has(input[value="Xanh"]) { color: #2563eb; border-color: #93c5fd; }
.products-page .checkbox-group label:has(input[value="Vàng"]) { color: #ca8a04; border-color: #fde047; }
.products-page .checkbox-group label:has(input[value="Hồng"]) { color: #ec4899; border-color: #fbcfe8; }
.products-page .checkbox-group label:has(input[value="Tím"]) { color: #8b5cf6; border-color: #c4b5fd; }

/* Thêm dấu chấm tròn màu bên cạnh chữ */
.products-page .checkbox-group label:has(input[value="Trắng"])::before { content: "●"; color: #9ca3af; margin-right: 6px; font-size: 14px; }
.products-page .checkbox-group label:has(input[value="Đen"])::before { content: "●"; color: #1f2937; margin-right: 6px; font-size: 14px; }
.products-page .checkbox-group label:has(input[value="Đỏ"])::before { content: "●"; color: #ef4444; margin-right: 6px; font-size: 14px; }
.products-page .checkbox-group label:has(input[value="Xanh"])::before { content: "●"; color: #3b82f6; margin-right: 6px; font-size: 14px; }
.products-page .checkbox-group label:has(input[value="Vàng"])::before { content: "●"; color: #eab308; margin-right: 6px; font-size: 14px; }
.products-page .checkbox-group label:has(input[value="Hồng"])::before { content: "●"; color: #ec4899; margin-right: 6px; font-size: 14px; }
.products-page .checkbox-group label:has(input[value="Tím"])::before { content: "●"; color: #a855f7; margin-right: 6px; font-size: 14px; }

.products-page .checkbox-group label:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.products-page .checkbox-group input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: #2563eb;
    cursor: pointer;
    margin: 0;
}

.products-page input[type="file"] {
    padding: 10px 0;
    border: none;
}

.products-page input[type="file"]::file-selector-button {
    padding: 8px 20px;
    background: linear-gradient(135deg, #bfdbfe, #93c5fd);
    border: none;
    border-radius: 40px;
    color: #1e3a8a;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s;
}

.products-page input[type="file"]::file-selector-button:hover {
    background: linear-gradient(135deg, #93c5fd, #60a5fa);
    transform: scale(1.02);
}

.products-page .form-actions {
    display: flex;
    gap: 16px;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid rgba(37, 99, 235, 0.2);
    justify-content: flex-end;
}

.products-page .btn-primary {
    background: linear-gradient(135deg, #1e3a8a, #2563eb, #3b82f6);
    color: white;
    padding: 12px 36px;
    border-radius: 40px;
    border: none;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 6px 14px rgba(37, 99, 235, 0.35);
}

.products-page .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.45);
}

.products-page .btn-secondary {
    background: #f1f5f9;
    color: #475569;
    padding: 12px 28px;
    border-radius: 40px;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.25s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.products-page #currentImagePreview img {
    border-radius: 16px;
    border: 2px solid #e2e8f0;
    padding: 4px;
    max-width: 100px;
    margin-top: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .products-page {
        padding: 10px;
    }
    
    .products-page .card-body {
        padding: 24px 18px;
    }
    
    .products-page .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .products-page .page-header {
        flex-direction: column;
        text-align: center;
    }
    
    .products-page .page-header h1 {
        font-size: 26px;
    }
    
    .products-page .checkbox-group label {
        padding: 5px 12px;
        font-size: 12px;
    }
    
    .products-page .form-actions {
        flex-direction: column;
    }
    
    .products-page .btn-primary,
    .products-page .btn-secondary {
        justify-content: center;
        width: 100%;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .products-page {
        max-width: 95%;
    }
    
    .products-page .card-body {
        padding: 35px 30px;
    }
}
</style>
<div class="products-page">
    <div class="page-header">
        <h1>Thêm sản phẩm mới</h1>
        <a href="<?php echo BASE_URL; ?>/index.php?url=admin/products" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>
    <div class="card">
        <div class="card-body">
            <form id="createProductForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Tên sản phẩm</label>
                    <input type="text" name="name" required>
                </div>
                
                <div class="form-row">
    <div class="form-group">
        <label>Giá (VNĐ)</label>
        <input type="number" name="price" required>
    </div>
    <div class="form-group">
        <label>Giá cọc (VNĐ)</label>
        <input type="number" name="deposit_price" required>
    </div>
    <div class="form-group">
        <label>Tồn kho</label>
        <input type="number" name="stock" required>
    </div>
</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Giới tính</label>
                        <select name="gender" class="form-control">
                            <option value="unisex">Unisex</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Số lần thuê</label>
                        <input type="number" name="rented_count" value="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Nhóm danh mục</label>
                    <select id="parentCat" class="form-control" required>
                        <option value="">-- Chọn nhóm --</option>
                        <option value="1">🔥 TRENDING</option>
                        <option value="2">🆕 NEW</option>
                        <option value="3">👕 SET OUTFIT</option>
                        <option value="4">💰 SALE</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Danh mục con</label>
                    <select name="category_id" id="childCat" class="form-control" required>
                        <option value="">-- Chọn danh mục --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Màu sắc (chọn nhiều)</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="colors[]" value="Trắng"> Trắng</label>
                        <label><input type="checkbox" name="colors[]" value="Đen"> Đen</label>
                        <label><input type="checkbox" name="colors[]" value="Đỏ"> Đỏ</label>
                        <label><input type="checkbox" name="colors[]" value="Xanh"> Xanh</label>
                        <label><input type="checkbox" name="colors[]" value="Vàng"> Vàng</label>
                        <label><input type="checkbox" name="colors[]" value="Hồng"> Hồng</label>
                        <label><input type="checkbox" name="colors[]" value="Tím"> Tím</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Kích thước (chọn nhiều)</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="sizes[]" value="S"> S</label>
                        <label><input type="checkbox" name="sizes[]" value="M"> M</label>
                        <label><input type="checkbox" name="sizes[]" value="L"> L</label>
                        <label><input type="checkbox" name="sizes[]" value="XL"> XL</label>
                        <label><input type="checkbox" name="sizes[]" value="XXL"> XXL</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Ảnh sản phẩm</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                    <a href="<?php echo BASE_URL; ?>/index.php?url=admin/products" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const childMap = {
    1: [
        { id: 5, name: 'Street Style' },
        { id: 6, name: 'Tối Giản' },
        { id: 7, name: 'Y2K' },
        { id: 8, name: 'Style Hàn' }
    ],
    2: [
        { id: 9, name: 'Vừa Ra Mắt' },
        { id: 10, name: 'Giới Hạn' }
    ],
    3: [
        { id: 11, name: 'Năng Động' },
        { id: 12, name: 'Thanh Lịch' },
        { id: 13, name: 'Cá Tính' },
        { id: 14, name: 'Quyến Rũ' },
        { id: 15, name: 'Dễ Thương' }
    ],
    4: [
        { id: 16, name: 'Combo Tiết Kiệm' },
        { id: 17, name: 'Mã Giảm Giá' },
        { id: 18, name: 'Ưu Đãi Khách Mới' }
    ]
};

const parentSelect = document.getElementById('parentCat');
const childSelect = document.getElementById('childCat');

parentSelect.addEventListener('change', function() {
    const parentId = this.value;
    childSelect.innerHTML = '<option value="">-- Chọn danh mục --</option>';
    
    if (parentId && childMap[parentId]) {
        childMap[parentId].forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            childSelect.appendChild(option);
        });
    }
});

document.getElementById('createProductForm').onsubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await fetch('http://localhost:8080/LoopFit/public/index.php?url=admin/products/store', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
        alert('Thêm sản phẩm thành công');
        window.location.href = 'http://localhost:8080/LoopFit/public/index.php?url=admin/products';
    } else {
        alert(data.error || 'Lỗi khi thêm');
    }
};
</script>