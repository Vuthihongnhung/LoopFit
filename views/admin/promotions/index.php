<?php $pageTitle = 'Quản lý khuyến mãi'; $activeMenu = 'promotions'; ?>
<div class="promotions-page">
    <div class="page-header">
        <h1>Mã giảm giá</h1>
        <button class="btn btn-primary" id="openPromoModalBtn"><i class="fas fa-plus"></i> Thêm mã</button>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="data-table" id="promotionsTable">
                <thead><tr><th>ID</th><th>Mã</th><th>Giảm giá (%)</th><th>Ngày bắt đầu</th><th>Ngày kết thúc</th><th>Trạng thái</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<div id="promoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h2>Thêm mã giảm giá</h2><span class="close">&times;</span></div>
        <form id="promoForm">
            <div class="form-group"><label>Mã code</label><input type="text" name="code" required></div>
            <div class="form-group"><label>Giảm giá (%)</label><input type="number" name="discount_percent" min="1" max="100" required></div>
            <div class="form-row"><div class="form-group"><label>Ngày bắt đầu</label><input type="datetime-local" name="start_date"></div>
            <div class="form-group"><label>Ngày kết thúc</label><input type="datetime-local" name="end_date"></div></div>
            <div class="form-actions"><button type="submit" class="btn btn-primary">Lưu</button><button type="button" class="btn btn-secondary close-modal">Hủy</button></div>
        </form>
    </div>
</div>
<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
    function loadPromotions() {
        fetch(BASE_URL + '/admin/api/promotions')
            .then(res => res.json())
            .then(data => {
                let html = '';
                data.data.forEach(p => {
                    let statusClass = p.status === 'active' ? 'active' : (p.status === 'upcoming' ? 'upcoming' : 'expired');
                    html += `<tr>
                        <td>${p.id}</td>
                        <td>${p.code}</td>
                        <td>${p.discount_percent}%</td>
                        <td>${p.start_date}</td>
                        <td>${p.end_date}</td>
                        <td><span class="badge ${statusClass}">${p.status}</span></td>
                    </tr>`;
                });
                document.querySelector('#promotionsTable tbody').innerHTML = html;
            });
    }
    document.getElementById('openPromoModalBtn').onclick = () => document.getElementById('promoModal').style.display = 'block';
    document.getElementById('promoForm').onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        fetch(BASE_URL + '/admin/promotions/store', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('promoModal').style.display = 'none';
                    loadPromotions();
                    e.target.reset();
                } else alert(data.error);
            });
    };
    document.querySelectorAll('.close, .close-modal').forEach(el => {
        el.onclick = () => document.getElementById('promoModal').style.display = 'none';
    });
    loadPromotions();
</script>