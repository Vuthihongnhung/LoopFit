<?php include "../includes/header.php"; ?>
<?php include "../includes/navbar.php"; ?>

<main>

<!-- TITLE -->
<h2 style="margin:20px;">Sale</h2>

<!-- FILTER -->
<div class="filter">
    <button class="filter-btn active" data-filter="all">Tất cả</button>
    <button class="filter-btn" data-filter="nam">Nam</button>
    <button class="filter-btn" data-filter="nu">Nữ</button>
</div>

<!-- DANH SÁCH SẢN PHẨM -->
<div class="product-list">

    <!-- NAM -->
    <div class="product" data-category="nam">
        Áo gym nam bán chạy
    </div>

    <div class="product" data-category="nam">
        Quần short nam hot
    </div>

    <!-- NỮ -->
    <div class="product" data-category="nu">
        Set yoga nữ bán chạy
    </div>

    <div class="product" data-category="nu">
        Legging nữ hot
    </div>

</div>

</main>

<script src="../js/filter.js"></script>

<?php include "../includes/footer.php"; ?>