<?php 
$pageTitle = 'Quản lý đánh giá'; 
$activeMenu = 'reviews';
?>
<div class="reviews-page">
    <div class="page-header">
        <h1><i class="fas fa-star"></i> Quản lý đánh giá</h1>
        <div class="search-box">
            <input type="text" id="searchReview" placeholder="Tìm kiếm theo sản phẩm, người dùng..." class="form-control">
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Danh sách đánh giá</h3>
            <span class="badge active" id="reviewCount">0 đánh giá</span>
        </div>
        <div class="card-body" style="padding: 20px; overflow-x: auto;">
            <table class="data-table" id="reviewsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sản phẩm</th>
                        <th>Người dùng</th>
                        <th>Đánh giá</th>
                        <th>Bình luận</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="8" style="text-align: center;">Đang tải...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// ========== REVIEWS ==========
const BASE_URL = '<?php echo BASE_URL; ?>';

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function formatDate(dateString) {
    if (!dateString) return '---';
    let date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN');
}

function getStarsHtml(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<i class="fas fa-star" style="color: #fbbf24;"></i>';
        } else {
            stars += '<i class="far fa-star" style="color: #fbbf24;"></i>';
        }
    }
    return stars;
}

function showToast(message, type) {
    var toast = document.createElement('div');
    toast.className = 'toast-message';
    toast.style.backgroundColor = type === 'success' ? '#28a745' : '#dc3545';
    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.right = '20px';
    toast.style.padding = '12px 24px';
    toast.style.borderRadius = '8px';
    toast.style.color = 'white';
    toast.style.zIndex = '9999';
    toast.innerHTML = message;
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 3000);
}

let reviewsData = [];

function loadReviews() {
    fetch(BASE_URL + '/index.php?url=admin/api/reviews')
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                reviewsData = data.data;
                renderReviewsTable();
            } else {
                document.querySelector('#reviewsTable tbody').innerHTML = '<tr><td colspan="8" class="text-center">❌ Lỗi tải dữ liệu</td></tr>';
            }
        })
        .catch(err => {
            console.error(err);
            document.querySelector('#reviewsTable tbody').innerHTML = '<tr><td colspan="8" class="text-center">❌ Lỗi kết nối</td></tr>';
        });
}

function renderReviewsTable() {
    const searchTerm = document.getElementById('searchReview')?.value.toLowerCase() || '';
    
    let filtered = reviewsData.filter(review => 
        (review.product_name || '').toLowerCase().includes(searchTerm) ||
        (review.user_name || '').toLowerCase().includes(searchTerm) ||
        (review.comment || '').toLowerCase().includes(searchTerm)
    );
    
    document.getElementById('reviewCount').innerText = filtered.length + ' đánh giá';
    
    let html = '';
    if(filtered.length > 0) {
        filtered.forEach(review => {
            const isHidden = review.is_hidden == 1;
            const statusText = isHidden ? 'Đã ẩn' : 'Hiển thị';
            const statusClass = isHidden ? 'status-hidden' : 'status-visible';
            
            let starsHtml = getStarsHtml(review.rating);
            
            html += `<tr>
                <td>${review.id}</td>
                <td><strong>${escapeHtml(review.product_name || 'N/A')}</strong></td>
                <td>${escapeHtml(review.user_name || 'N/A')}<br><small>${escapeHtml(review.email || '')}</small></td>
                <td>${starsHtml}</td>
                <td><div class="comment-cell" title="${escapeHtml(review.comment || '')}">${escapeHtml(review.comment || '')}</div></td>
                <td>${formatDate(review.created_at)}</td>
                <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                <td>
                    <button class="btn-icon btn-toggle" onclick="toggleReviewStatus(${review.id}, ${review.is_hidden})" title="Ẩn/Hiện">
                        <i class="fas ${isHidden ? 'fa-eye-slash' : 'fa-eye'}"></i>
                    </button>
                    <button class="btn-icon delete" onclick="deleteReview(${review.id})" title="Xóa">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        });
    } else {
        html = '<tr><td colspan="8" class="text-center">📭 Không tìm thấy đánh giá nào</td></tr>';
    }
    document.querySelector('#reviewsTable tbody').innerHTML = html;
}

function toggleReviewStatus(id, currentStatus) {
    const action = currentStatus == 1 ? 'hiện' : 'ẩn';
    if (!confirm(`Xác nhận ${action} đánh giá này?`)) return;
    
    fetch(BASE_URL + '/index.php?url=admin/reviews/toggle-status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&current_status=${currentStatus}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            loadReviews();
        } else {
            showToast(data.error || 'Cập nhật thất bại', 'error');
        }
    })
    .catch(err => showToast('Lỗi kết nối', 'error'));
}

function deleteReview(id) {
    if (!confirm('Bạn có chắc muốn xóa đánh giá này?')) return;
    
    fetch(BASE_URL + '/index.php?url=admin/reviews/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            loadReviews();
        } else {
            showToast(data.error || 'Xóa thất bại', 'error');
        }
    })
    .catch(err => showToast('Lỗi kết nối', 'error'));
}

// Khởi tạo khi trang load
document.addEventListener('DOMContentLoaded', function() {
    loadReviews();
    document.getElementById('searchReview')?.addEventListener('keyup', renderReviewsTable);
});
</script>