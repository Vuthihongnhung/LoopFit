<?php 
$pageTitle = 'Quản lý danh mục'; 
$activeMenu = 'categories';
?>
<div class="categories-page">
    <div class="page-header">
        <h1><i class="fas fa-folder-tree"></i> Danh mục sản phẩm</h1>
        <button class="btn btn-primary" id="openModalBtn"><i class="fas fa-plus"></i> Thêm danh mục</button>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="data-table" id="categoriesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên danh mục</th>
                        <th>Slug</th>
                        <th>Danh mục cha</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="5">Đang tải...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal thêm/sửa danh mục -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Thêm danh mục</h2>
            <span class="close">&times;</span>
        </div>
        <form id="categoryForm">
            <input type="hidden" name="id" id="categoryId">
            <div class="form-group">
                <label>Tên danh mục</label>
                <input type="text" name="name" id="categoryName" required>
            </div>
            <div class="form-group">
                <label>Danh mục cha</label>
                <select name="parent_id" id="categoryParent">
                    <option value="0">-- Danh mục cha (cấp 1) --</option>
                </select>
            </div>
            <div class="form-group">
                <label>Icon (Font Awesome)</label>
                <input type="text" name="icon" id="categoryIcon" placeholder="VD: fa-fire, fa-star">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Lưu</button>
                <button type="button" class="btn btn-secondary close-modal">Hủy</button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';
let editingId = null;

function loadCategories() {
    fetch(BASE_URL + '/index.php?url=admin/api/categories')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if(data.success && data.data.length) {
                data.data.forEach(parent => {
                    html += `<tr style="background:#f0f4f8;">
                        <td>${parent.id}</td>
                        <td><strong>📁 ${parent.name}</strong></td>
                        <td>${parent.slug}</td>
                        <td>--</td>
                        <td>
                            <button class="btn-icon edit-cat" data-id="${parent.id}" data-name="${parent.name}" data-parent="0" data-icon="${parent.icon || ''}"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon delete-cat" data-id="${parent.id}"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`;
                    if(parent.children && parent.children.length) {
                        parent.children.forEach(child => {
                            html += `<tr>
                                <td>${child.id}</td>
                                <td>└─ ${child.name}</td>
                                <td>${child.slug}</td>
                                <td>${parent.name}</td>
                                <td>
                                    <button class="btn-icon edit-cat" data-id="${child.id}" data-name="${child.name}" data-parent="${parent.id}" data-icon="${child.icon || ''}"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon delete-cat" data-id="${child.id}"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>`;
                        });
                    }
                });
            } else {
                html = '<tr><td colspan="5">Chưa có danh mục nào</td></tr>';
            }
            document.querySelector('#categoriesTable tbody').innerHTML = html;
            attachEvents();
        });
}

function attachEvents() {
    document.querySelectorAll('.edit-cat').forEach(btn => {
        btn.onclick = () => {
            editingId = btn.dataset.id;
            document.getElementById('modalTitle').innerText = 'Sửa danh mục';
            document.getElementById('categoryId').value = btn.dataset.id;
            document.getElementById('categoryName').value = btn.dataset.name;
            document.getElementById('categoryParent').value = btn.dataset.parent;
            document.getElementById('categoryIcon').value = btn.dataset.icon || '';
            document.getElementById('categoryModal').style.display = 'block';
        };
    });
    document.querySelectorAll('.delete-cat').forEach(btn => {
        btn.onclick = () => {
            if(confirm('Bạn có chắc muốn xóa danh mục này?')) {
                fetch(BASE_URL + '/index.php?url=admin/categories/delete', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `id=${btn.dataset.id}`
                }).then(res => res.json()).then(data => {
                    if(data.success) {
                        alert(data.message);
                        loadCategories();
                    } else alert(data.error);
                });
            }
        };
    });
}

// Load danh sách danh mục cha cho select
function loadParentOptions() {
    fetch(BASE_URL + '/index.php?url=admin/api/categories')
        .then(res => res.json())
        .then(data => {
            let html = '<option value="0">-- Danh mục cha (cấp 1) --</option>';
            if(data.success && data.data.length) {
                data.data.forEach(parent => {
                    html += `<option value="${parent.id}">${parent.name}</option>`;
                });
            }
            document.getElementById('categoryParent').innerHTML = html;
        });
}

document.getElementById('openModalBtn').onclick = () => {
    editingId = null;
    document.getElementById('modalTitle').innerText = 'Thêm danh mục';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryParent').value = '0';
    document.getElementById('categoryModal').style.display = 'block';
};

document.getElementById('categoryForm').onsubmit = (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const url = editingId ? BASE_URL + '/index.php?url=admin/categories/update' : BASE_URL + '/index.php?url=admin/categories/store';
    fetch(url, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('categoryModal').style.display = 'none';
                alert(data.message);
                loadCategories();
                loadParentOptions();
            } else alert(data.error);
        });
};

document.querySelectorAll('.close, .close-modal').forEach(el => {
    el.onclick = () => document.getElementById('categoryModal').style.display = 'none';
});

loadCategories();
loadParentOptions();
</script>