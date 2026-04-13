<?php 
$pageTitle = 'Quản lý khuyến mãi'; 
$activeMenu = 'promotions';
$conn = (new Core\Database())->getConnection();
?>
<div class="promotions-page">
    <div class="page-header">
        <h1><i class="fas fa-tags"></i> Khuyến mãi</h1>
        <button class="btn btn-primary" id="openModalBtn">
            <i class="fas fa-plus"></i> Thêm mã giảm giá
        </button>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Danh sách mã giảm giá</h3>
            <div class="search-box">
                <input type="text" id="searchPromo" placeholder="🔍 Tìm kiếm mã..." class="form-control">
            </div>
        </div>
        <div class="card-body" style="display: flex; justify-content: center; padding: 20px;">
            <div style="width: 100%; max-width: 1200px; overflow-x: auto;">
                <table class="data-table" id="promotionsTable" style="width: 100%; margin: 0 auto;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mã code</th>
                            <th>Giảm giá</th>
                            <th>Ngày bắt đầu</th>
                            <th>Ngày kết thúc</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="7" style="text-align: center;">📦 Đang tải...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.search-box input {
    border-radius: 40px;
    padding: 8px 16px;
    border: 1px solid #e2e8f0;
    width: 250px;
}
.discount-badge {
    background: #2563eb;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
}
</style>

<!-- Modal thêm khuyến mãi -->
<div id="promoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-plus-circle"></i> Thêm mã giảm giá</h2>
            <span class="close">&times;</span>
        </div>
        <form id="promoForm">
            <div class="form-group">
                <label><i class="fas fa-barcode"></i> Mã code</label>
                <input type="text" name="code" id="promoCode" placeholder="Nhập mã (VD: SALE20)" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-percent"></i> Giảm giá (%)</label>
                <input type="number" name="discount_percent" id="promoDiscount" min="1" max="100" placeholder="1-100" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Ngày bắt đầu</label>
                    <input type="datetime-local" name="start_date" id="promoStart">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar-check"></i> Ngày kết thúc</label>
                    <input type="datetime-local" name="end_date" id="promoEnd">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                <button type="button" class="btn btn-secondary close-modal"><i class="fas fa-times"></i> Hủy</button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';
let promotionsData = [];

function loadPromotions() {
    fetch(BASE_URL + '/index.php?url=admin/api/promotions')
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                promotionsData = data.data;
                renderPromotionsTable();
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
            document.querySelector('#promotionsTable tbody').innerHTML = '<tr><td colspan="7" style="text-align: center;">❌ Lỗi tải dữ liệu</td></tr>';
        });
}

function renderPromotionsTable() {
    const searchTerm = document.getElementById('searchPromo')?.value.toLowerCase() || '';
    
    let filtered = promotionsData.filter(p => 
        p.code.toLowerCase().includes(searchTerm)
    );
    
    let html = '';
    if(filtered.length > 0) {
        filtered.forEach(p => {
            let statusClass = p.status === 'active' ? 'active' : (p.status === 'upcoming' ? 'upcoming' : 'expired');
            let statusText = p.status === 'active' ? '🟢 Đang diễn ra' : (p.status === 'upcoming' ? '🔜 Sắp diễn ra' : '🔚 Đã kết thúc');
            
            html += `<tr>
                <td>${p.id}</td>
                <td><strong class="discount-badge">${p.code}</strong></td>
                <td><span class="badge active" style="background:#2563eb;">${p.discount_percent}%</span></td>
                <td>${p.start_date || 'N/A'}</td>
                <td>${p.end_date || 'N/A'}</td>
                <td><span class="badge ${statusClass}">${statusText}</span></td>
                <td>
                    <button class="btn-icon delete-promo" data-id="${p.id}" data-code="${p.code}" title="Xóa"><i class="fas fa-trash"></i></button>
                 </td>
             </tr>`;
        });
    } else {
        html = '<tr><td colspan="7" style="text-align: center;">📭 Không có mã giảm giá nào</td></tr>';
    }
    document.querySelector('#promotionsTable tbody').innerHTML = html;
    attachEvents();
}

function attachEvents() {
    document.querySelectorAll('.delete-promo').forEach(btn => {
        btn.onclick = () => deletePromotion(btn.dataset.id, btn.dataset.code);
    });
}

function deletePromotion(id, code) {
    if(confirm(`Bạn có chắc muốn xóa mã "${code}"?`)) {
        fetch(BASE_URL + '/index.php?url=admin/promotions/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}`
        }).then(res => res.json())
        .then(data => {
            if(data.success) {
                showToast('Xóa mã thành công', 'success');
                loadPromotions();
            } else alert('Xóa thất bại');
        });
    }
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'toast-message';
    toast.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
    toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Search
document.getElementById('searchPromo')?.addEventListener('keyup', renderPromotionsTable);

// Modal
document.getElementById('openModalBtn').onclick = () => {
    document.getElementById('promoForm').reset();
    document.getElementById('promoModal').style.display = 'block';
};

document.getElementById('promoForm').onsubmit = (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    fetch(BASE_URL + '/index.php?url=admin/promotions/store', {
        method: 'POST',
        body: formData
    }).then(res => res.json())
    .then(data => {
        if(data.success) {
            document.getElementById('promoModal').style.display = 'none';
            showToast('Thêm mã thành công', 'success');
            loadPromotions();
        } else alert(data.error);
    });
};

document.querySelectorAll('.close, .close-modal').forEach(el => {
    el.onclick = () => document.getElementById('promoModal').style.display = 'none';
});

loadPromotions();
</script>