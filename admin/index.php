<?php include "includes/header.php"; ?>
<?php include "includes/sidebar.php"; ?>

<div class="content-wrapper">

    <!-- MAIN -->
    <div class="main">

        <div class="search-bar">
            <input type="text" placeholder="Tìm sản phẩm...">
        </div>

        <div class="products">
            <div class="card">
                <img src="https://via.placeholder.com/200">
                <h4>Áo Gym</h4>
                <p>150.000đ</p>
            </div>

            <div class="card">
                <img src="https://via.placeholder.com/200">
                <h4>Quần Short</h4>
                <p>120.000đ</p>
            </div>
        </div>

    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
        <h3>Chi tiết đơn</h3>
        <p>Giỏ hàng trống</p>
    </div>

</div>

<!-- SCRIPT -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.querySelector('.menu-toggle');

    if (btn) {
        btn.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-hide');
        });
    }
});
</script>

</body>
</html>