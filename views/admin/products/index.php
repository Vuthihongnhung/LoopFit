<?php 
$pageTitle = 'Quản lý sản phẩm'; 
$activeMenu = 'products';
?>
<script>const BASE_URL = 'http://localhost:8080/LoopFit/public';</script>
<div class="products-page">
    <div class="page-header">
        <h1><i class="fas fa-box"></i> Sản phẩm</h1>
        <a href="<?php echo BASE_URL; ?>/index.php?url=admin/products/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm sản phẩm
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Danh sách sản phẩm</h3>
            <div class="filter-bar" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <!-- Tìm kiếm -->
                <div class="search-box">
                    <input type="text" id="searchProduct" placeholder="Tìm kiếm sản phẩm..." class="form-control">
                </div>
                
                <!-- Lọc theo giới tính -->
                <select id="filterGender" class="form-control" style="width: auto;">
                    <option value="">Tất cả giới tính</option>
                    <option value="male">👨 Nam</option>
                    <option value="female">👩 Nữ</option>
                    <option value="unisex">🔄 Unisex</option>
                </select>
                
                <!-- Lọc theo nhóm danh mục -->
                <select id="filterCategoryGroup" class="form-control" style="width: auto;">
                    <option value="">Tất cả nhóm</option>
                    <option value="1">🔥 TRENDING</option>
                    <option value="2">🆕 NEW</option>
                    <option value="3">👕 SET OUTFIT</option>
                    <option value="4">💰 SALE</option>
                </select>
                
                <button id="resetFilter" class="btn btn-secondary" style="padding: 8px 16px;">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table" id="productsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Giá</th>
                            <th>Giảm giá (%)</th>
                            <th>Giá cọc</th>
                            <th>Tồn kho</th>
                            <th>Danh mục</th>
                            <th>Giới tính</th>
                            <th>Màu sắc</th>
                            <th>Kích thước</th>
                            <th>Số lần thuê</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="13" class="text-center">Đang tải...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>