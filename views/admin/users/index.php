<?php $pageTitle = 'Quản lý người dùng'; $activeMenu = 'users'; ?>
<div class="users-page">
    <div class="page-header">
        <h1>Người dùng</h1>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="data-table" id="usersTable">
                <thead>
                    <tr><th>ID</th><th>Tên</th><th>Email</th><th>Ngày đăng ký</th><th>Trạng thái</th><th>Thao tác</th></tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
    function loadUsers() {
        fetch(BASE_URL + '/admin/api/users')
            .then(res => res.json())
            .then(data => {
                let html = '';
                if (data.data && data.data.length) {
                    data.data.forEach(user => {
                        let status = user.status || 'active';
                        let statusText = status === 'active' ? 'Hoạt động' : 'Bị khóa';
                        let badgeClass = status === 'active' ? 'active' : 'banned';
                        html += `<tr>
                            <td>${user.id}</td>
                            <td>${user.name}</td>
                            <td>${user.email}</td>
                            <td>${user.created_at}</td>
                            <td><span class="badge ${badgeClass}">${statusText}</span></td>
                            <td><button class="btn-icon toggle-status" data-id="${user.id}"><i class="fas ${status === 'active' ? 'fa-ban' : 'fa-check-circle'}"></i> ${status === 'active' ? 'Khóa' : 'Mở'}</button></td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="6">Không có người dùng nào</td><tr>';
                }
                document.querySelector('#usersTable tbody').innerHTML = html;
                document.querySelectorAll('.toggle-status').forEach(btn => {
                    btn.onclick = () => toggleUserStatus(btn.dataset.id);
                });
            });
    }
    function toggleUserStatus(id) {
        fetch(BASE_URL + '/admin/users/toggle-status', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}`
        }).then(res => res.json()).then(data => {
            if(data.success) loadUsers();
            else alert('Thao tác thất bại');
        });
    }
    loadUsers();
</script>