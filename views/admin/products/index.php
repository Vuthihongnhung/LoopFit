<?php 
$pageTitle = 'Quản lý sản phẩm'; 
$activeMenu = 'products';
$baseUrl = BASE_URL ?? '/LoopFit/public';
?>
<script>
    const BASE_URL = '<?php echo $baseUrl; ?>';
</script>
<div class="products-page">
    <div class="page-header">
        <h1><i class="fas fa-box"></i> Sản phẩm</h1>
        <a href="<?php echo $baseUrl; ?>/admin/products/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm sản phẩm
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Danh sách sản phẩm</h3>
            <div class="filter-bar" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <div class="search-box">
                    <input type="text" id="searchProduct" placeholder="Tìm kiếm sản phẩm..." class="form-control">
                </div>
                <select id="filterGender" class="form-control" style="width: auto;">
                    <option value="">Tất cả giới tính</option>
                    <option value="male">👨 Nam</option>
                    <option value="female">👩 Nữ</option>
                    <option value="unisex">🔄 Unisex</option>
                </select>
                <select id="filterCategoryGroup" class="form-control" style="width: auto;">
                    <option value="">Tất cả nhóm</option>
                    <?php if (!empty($parentCategories)): ?>
                        <?php foreach ($parentCategories as $cat): ?>
                            <option value="<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Chưa có danh mục</option>
                    <?php endif; ?>
                </select>
                <button id="resetFilter" class="btn btn-secondary" style="padding: 8px 16px;">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="data-table" id="productsTable" style="min-width: 1200px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Giá</th>
                            <th>Giảm giá (%)</th>
                            <th>Giá cọc</th>
                            <th>Tồn kho</th>
                            <th>Đang thuê</th>
                            <th>Còn lại</th>
                            <th>Trạng thái</th>
                            <th>Danh mục</th>
                            <th>Giới tính</th>
                            <th>Chi tiết kho</th>
                            <th>Số lần thuê</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): 
                                $stock = (int)($product['stock'] ?? 0);
                                $currentRented = max(0, (int)($product['current_rented'] ?? 0));
                                $available = max(0, $product['stock'] - $currentRented);
                                
                                if ($available > 0) {
                                    $status = 'Còn hàng';
                                    $statusClass = 'status-instock';
                                } elseif ($currentRented > 0) {
                                    $status = 'Đang được thuê';
                                    $statusClass = 'status-rented';
                                } else {
                                    $status = 'Hết hàng';
                                    $statusClass = 'status-outstock';
                                }
                            ?>
                                <tr data-gender="<?= htmlspecialchars($product['gender'] ?? 'unisex') ?>" 
                                    data-category-id="<?= (int)($product['category_id'] ?? 0) ?>"
                                    data-product-name="<?= htmlspecialchars($product['name'] ?? '') ?>">
                                    <td><?= (int)($product['id'] ?? 0) ?></td>
                                    <td>
                                        <?php 
                                        $imgUrl = $product['image_url'] ?? '';
                                        if (!empty($imgUrl)): ?>
                                            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($product['name'] ?? '') ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <span class="no-image">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($product['name'] ?? '') ?></td>
                                    <td><?= number_format($product['price'] ?? 0, 0, ',', '.') ?>₫</td>
                                    <td class="discount-cell"><?= (int)($product['discount_percent'] ?? 0) ?>%</td>
                                    <td><?= number_format($product['deposit_price'] ?? 0, 0, ',', '.') ?>₫</td>
                                    <td><?= $stock ?></td>
                                    <td><?= $currentRented ?></td>
                                    <td><?= $available ?></td>
                                    <td class="<?= $statusClass ?>">
                                        <?= $status ?>
                                        <?php if ($available > 0): ?>
                                            <span class="stock-badge">(còn <?= $available ?>)</span>
                                        <?php elseif ($currentRented > 0): ?>
                                            <span class="rented-badge">(<?= $currentRented ?> đang thuê)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($product['category_name'] ?? '') ?></td>
                                    <td><?= ucfirst($product['gender'] ?? 'unisex') ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info view-stock-details" data-id="<?= $product['id'] ?>" title="Chi tiết kho">
                                            👁 Chi tiết kho
                                        </button>
                                    </td>
                                    <td><?= (int)($product['rented_count'] ?? 0) ?></td>
                                    <td class="actions">
                                        <a href="<?= $baseUrl ?>/admin/products/edit?id=<?= $product['id'] ?>" class="btn btn-sm btn-warning" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger delete-product" data-id="<?= $product['id'] ?>" data-name="<?= htmlspecialchars($product['name'] ?? '') ?>" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="15" class="text-center">Chưa có sản phẩm nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="inventoryModal" class="modal-overlay" style="display:none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Chi tiết tồn kho</h3>
            <button type="button" id="closeInventoryModal" class="btn-close">×</button>
        </div>
        <div class="modal-body">
            <div id="inventoryProductName" style="margin-bottom:10px;font-weight:700;color:#111827;"></div>
            <div class="table-responsive">
                <table class="data-table" id="inventoryDetailTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Màu sắc</th>
                            <th>Kích thước</th>
                            <th>Tồn kho</th>
                            <th>Đang thuê</th>
                            <th>Còn lại</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <p id="inventoryTotal" style="margin-top:12px;font-weight:700;color:#111827;"></p>
        </div>
    </div>
</div>

<style>
.status-instock { color: #10b981; font-weight: 600; }
.status-rented { color: #f59e0b; font-weight: 600; }
.status-outstock { color: #ef4444; font-weight: 600; }
.stock-badge, .rented-badge { font-size: 11px; font-weight: normal; color: #6b7280; }
.view-stock-details {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    white-space: nowrap;
    min-width: 120px;
    padding: 7px 12px;
    border-radius: 8px;
    border: 1px solid rgba(56, 189, 248, 0.4);
    background-color: #38bdf8;
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}
.view-stock-details:hover {
    background-color: #0ea5e9;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.18);
}
.view-stock-details:focus {
    outline: none;
    box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.2);
}
.table-responsive { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 10px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
.data-table th { background-color: #f9fafb; font-weight: 600; }
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}
.modal-card {
    width: min(760px, 100%);
    background: white;
    border-radius: 20px;
    box-shadow: 0 30px 60px rgba(15, 23, 42, 0.2);
    overflow: hidden;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid #e5e7eb;
}
.modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: #111827;
}
.btn-close {
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 999px;
    font-size: 20px;
    width: 36px;
    height: 36px;
    cursor: pointer;
}
.modal-body {
    padding: 18px 24px 24px;
}
.modal-body .data-table th,
.modal-body .data-table td {
    padding: 10px 12px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchProduct');
    const genderSelect = document.getElementById('filterGender');
    const categorySelect = document.getElementById('filterCategoryGroup');
    const resetBtn = document.getElementById('resetFilter');
    const tableRows = document.querySelectorAll('#productTableBody tr');

    function filterTable() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const gender = genderSelect ? genderSelect.value : '';
        const categoryId = categorySelect ? categorySelect.value : '';

        tableRows.forEach(row => {
            let show = true;

            // Lọc theo tên sản phẩm 
            if (searchTerm) {
                const productName = row.getAttribute('data-product-name') || '';
                if (!productName.toLowerCase().includes(searchTerm)) show = false;
            }

            // Lọc theo giới tính
            if (show && gender) {
                const rowGender = row.getAttribute('data-gender') || '';
                if (rowGender !== gender) show = false;
            }

            // Lọc theo danh mục
            if (show && categoryId) {
                const rowCat = row.getAttribute('data-category-id') || '';
                if (rowCat !== categoryId) show = false;
            }

            row.style.display = show ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('keyup', filterTable);
    if (genderSelect) genderSelect.addEventListener('change', filterTable);
    if (categorySelect) categorySelect.addEventListener('change', filterTable);
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (genderSelect) genderSelect.value = '';
            if (categorySelect) categorySelect.value = '';
            filterTable();
        });
    }

    const inventoryModal = document.getElementById('inventoryModal');
    const closeInventoryModal = document.getElementById('closeInventoryModal');
    const inventoryTableBody = document.querySelector('#inventoryDetailTable tbody');
    const inventoryTotal = document.getElementById('inventoryTotal');
    const inventoryProductName = document.getElementById('inventoryProductName');

    function openInventoryModal() {
        inventoryModal.style.display = 'flex';
    }

    function closeModal() {
        inventoryModal.style.display = 'none';
    }

    closeInventoryModal.addEventListener('click', closeModal);
    inventoryModal.addEventListener('click', (event) => {
        if (event.target === inventoryModal) {
            closeModal();
        }
    });
});
</script>