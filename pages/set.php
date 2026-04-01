<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>

<main>

<!-- TITLE -->
<h2 style="margin:20px;">Set Outfit</h2>

<!-- FILTER -->
<div class="filter">
    <button class="filter-btn active" data-filter="all">Tất cả</button>
    <button class="filter-btn" data-filter="nam">Nam</button>
    <button class="filter-btn" data-filter="nu">Nữ</button>
</div>

<!-- DANH SÁCH SET -->
<div class="product-list">

    <!-- SET NAM -->
    <div class="product" data-category="nam">
        Set gym nam basic
    </div>

    <div class="product" data-category="nam">
        Set chạy bộ nam
    </div>

    <!-- SET NỮ -->
    <div class="product" data-category="nu">
        Set yoga nữ
    </div>

    <div class="product" data-category="nu">
        Set gym nữ năng động
    </div>

</div>

</main>

<script src="../js/filter.js"></script>

<?php include "../includes/footer.php"; ?>