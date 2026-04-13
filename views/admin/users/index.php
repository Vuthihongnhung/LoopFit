<?php 
$pageTitle = 'Quản lý người dùng'; 
$activeMenu = 'users';
$conn = (new Core\Database())->getConnection();
?>
<div class="users-page">
    <div class="page-header">
        <h1><i class="fas fa-users"></i> Người dùng</h1>
        <div class="header-actions">
            <div class="search-box">
                <input type="text" id="searchUser" placeholder="🔍 Tìm kiếm theo tên hoặc email..." class="form-control">
            </div>
            <div class="filter-box">
                <select id="filterStatus" class="form-control">
                    <option value="all">📋 Tất cả</option>
                    <option value="active">🟢 Đang hoạt động</option>
                    <option value="banned">🔴 Đã khóa</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Danh sách người dùng</h3>
            <span class="badge active" id="userCount">0 người dùng</span>
        </div>
        <div class="card-body" style="display: flex; justify-content: center; padding: 20px;">
            <div style="width: 100%; max-width: 1200px; overflow-x: auto;">
                <table class="data-table" id="usersTable" style="width: 100%; margin: 0 auto;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Avatar</th>
                            <th>Tên người dùng</th>
                            <th>Email</th>
                            <th>Ngày đăng ký</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="7" style="text-align: center;">📦 Đang tải dữ liệu...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.header-actions {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}
.search-box input, .filter-box select {
    border-radius: 40px;
    padding: 8px 16px;
    border: 1px solid #e2e8f0;
    min-width: 200px;
}
.filter-box select {
    background: white;
    cursor: pointer;
}
.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 1.2rem;
}
</style>

<!-- Modal xem chi tiết người dùng -->
<div id="userDetailModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2><i class="fas fa-user-circle"></i> Chi tiết người dùng</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body" id="userDetailBody">
            <!-- Nội dung sẽ load bằng AJAX -->
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';
let usersData = [];

function loadUsers() {
    fetch(BASE_URL + '/index.php?url=admin/api/users')
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                usersData = data.data;
                renderUsersTable();
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
            document.querySelector('#usersTable tbody').innerHTML = '<tr><td colspan="7" style="text-align: center;">❌ Lỗi tải dữ liệu</td></tr>';
        });
}

function renderUsersTable() {
    const searchTerm = document.getElementById('searchUser')?.value.toLowerCase() || '';
    const filterStatus = document.getElementById('filterStatus')?.value || 'all';
    
    let filtered = usersData.filter(user => {
        const matchSearch = user.name.toLowerCase().includes(searchTerm) || 
                           user.email.toLowerCase().includes(searchTerm);
        const matchStatus = filterStatus === 'all' || user.status === filterStatus;
        return matchSearch && matchStatus;
    });
    
    document.getElementById('userCount').innerText = `${filtered.length} người dùng`;
    
    let html = '';
    if(filtered.length > 0) {
        filtered.forEach(user => {
            let statusBadge = user.status === 'active' ? 
                '<span class="badge active"><i class="fas fa-check-circle"></i> Hoạt động</span>' : 
                '<span class="badge banned"><i class="fas fa-ban"></i> Đã khóa</span>';
            
            let avatarHtml = `<div class="avatar"><i class="fas fa-user"></i></div>`;
            
            html += `<tr>
                <td>${user.id}</td>
                <td>${avatarHtml}</td>
                <td><strong>${escapeHtml(user.name)}</strong></td>
                <td>${escapeHtml(user.email)}</td>
                <td>${user.created_at || 'N/A'}</td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn-icon view-user" data-id="${user.id}" title="Xem chi tiết"><i class="fas fa-eye"></i></button>
                    <button class="btn-icon toggle-status" data-id="${user.id}" data-status="${user.status}" title="${user.status === 'active' ? 'Khóa' : 'Mở khóa'}">
                        <i class="fas ${user.status === 'active' ? 'fa-lock' : 'fa-unlock-alt'}"></i>
                    </button>
                </td>
            </tr>`;
        });
    } else {
        html = '<tr><td colspan="7" style="text-align: center;">📭 Không tìm thấy người dùng nào</td></tr>';
    }
    document.querySelector('#usersTable tbody').innerHTML = html;
    attachEvents();
}

function escapeHtml(str) {
    if(!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if(m === '&') return '&amp;';
        if(m === '<') return '&lt;';
        if(m === '>') return '&gt;';
        return m;
    });
}

function attachEvents() {
    document.querySelectorAll('.toggle-status').forEach(btn => {
        btn.onclick = () => toggleUserStatus(btn.dataset.id, btn.dataset.status);
    });
    document.querySelectorAll('.view-user').forEach(btn => {
        btn.onclick = () => viewUserDetail(btn.dataset.id);
    });
}

function toggleUserStatus(id, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'banned' : 'active';
    const action = newStatus === 'active' ? 'Mở khóa' : 'Khóa';
    if(confirm(`Bạn có chắc muốn ${action} người dùng này?`)) {
        fetch(BASE_URL + '/index.php?url=admin/users/toggle-status', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}`
        }).then(res => res.json())
        .then(data => {
            if(data.success) {
                showToast(`${action} thành công`, 'success');
                loadUsers();
            } else alert('Thao tác thất bại');
        });
    }
}

function viewUserDetail(id) {
    const user = usersData.find(u => u.id == id);
    if(user) {
        let html = `
            <div style="text-align: center; margin-bottom: 20px;">
                <div class="avatar" style="width: 80px; height: 80px; font-size: 2.5rem; margin: 0 auto;">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h3>${escapeHtml(user.name)}</h3>
                <p>${escapeHtml(user.email)}</p>
            </div>
            <table style="width: 100%;">
                <tr><td><strong>ID:</strong></td><td>${user.id}</td></tr>
                <tr><td><strong>Trạng thái:</strong></td><td>${user.status === 'active' ? '🟢 Hoạt động' : '🔴 Đã khóa'}</td></tr>
                <tr><td><strong>Ngày đăng ký:</strong></td><td>${user.created_at || 'N/A'}</td></tr>
            </table>
        `;
        document.getElementById('userDetailBody').innerHTML = html;
        document.getElementById('userDetailModal').style.display = 'block';
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

// Search và filter
document.getElementById('searchUser')?.addEventListener('keyup', renderUsersTable);
document.getElementById('filterStatus')?.addEventListener('change', renderUsersTable);

// Modal close
document.querySelectorAll('.close').forEach(el => {
    el.onclick = () => document.getElementById('userDetailModal').style.display = 'none';
});
window.onclick = (e) => {
    if(e.target === document.getElementById('userDetailModal')) {
        document.getElementById('userDetailModal').style.display = 'none';
    }
};

loadUsers();
</script>