<div class="success-wrapper">
    <div class="success-container">
        <div class="success-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h1>Đặt hàng thành công!</h1>
        <p>Cảm ơn bạn đã thuê quần áo tại <strong>LoopFit</strong>.</p>
        <p>Mã đơn hàng: <strong>#<?= htmlspecialchars($order_id) ?></strong></p>
        <p>Chúng tôi sẽ liên hệ xác nhận và giao hàng trong thời gian sớm nhất.</p>
        <div class="action-buttons">
            <a href="<?= BASE_URL ?>/orders" class="btn-primary">Xem đơn hàng</a>
            <a href="<?= BASE_URL ?>/products" class="btn-outline">Tiếp tục mua sắm</a>
        </div>
    </div>
</div>

<style>
    .success-wrapper {
        max-width: 1200px;
        margin: 80px auto;
        padding: 0 20px;
        text-align: center;
        font-family: 'Montserrat', sans-serif;
    }
    .success-container {
        background: #fff;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .success-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }
    h1 {
        font-family: 'Playfair Display', serif;
        margin-bottom: 20px;
        color: #2c3e50;
    }
    p {
        font-size: 16px;
        margin-bottom: 16px;
        color: #555;
    }
    .action-buttons {
        margin-top: 30px;
        display: flex;
        gap: 16px;
        justify-content: center;
    }
    .btn-primary, .btn-outline {
        display: inline-block;
        padding: 10px 24px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 600;
        transition: 0.2s;
    }
    .btn-primary {
        background: #718BBC;
        color: white;
        border: none;
    }
    .btn-primary:hover {
        background: #5a6e9e;
    }
    .btn-outline {
        border: 1px solid #718BBC;
        color: #718BBC;
        background: transparent;
    }
    .btn-outline:hover {
        background: #718BBC;
        color: white;
    }
</style>