<!-- views/components/footer.php -->
<footer class="footer">
    <div class="footer__container">
        <!-- Cột 1: Giới thiệu -->
        <div class="footer__col">
            <h3 class="footer__logo">LoopFit</h3>
            <p class="footer__desc">
                Nền tảng cho thuê quần áo thông minh – Thời trang không cần sở hữu, phong cách không giới hạn.
            </p>
            <div class="footer__social">
                <a href="#" class="social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-link" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                <a href="#" class="social-link" aria-label="Zalo"><i class="fab fa-zalo"></i></a>
            </div>
        </div>

        <!-- Cột 2: Khám phá -->
        <div class="footer__col">
            <h4 class="footer__title">Khám phá</h4>
            <ul class="footer__links">
                <li><a href="/products">Bộ sưu tập</a></li>
                <li><a href="/about">Về LoopFit</a></li>
                <li><a href="/blog">Góc thời trang</a></li>
                <li><a href="/promotions">Khuyến mãi</a></li>
            </ul>
        </div>

        <!-- Cột 3: Hỗ trợ -->
        <div class="footer__col">
            <h4 class="footer__title">Hỗ trợ</h4>
            <ul class="footer__links">
                <li><a href="/faq">Câu hỏi thường gặp</a></li>
                <li><a href="/returns">Chính sách đổi trả</a></li>
                <li><a href="/delivery">Vận chuyển & Thuê</a></li>
                <li><a href="/contact">Liên hệ</a></li>
            </ul>
        </div>

        <!-- Cột 4: Liên hệ -->
        <div class="footer__col">
            <h4 class="footer__title">Liên hệ</h4>
            <ul class="footer__contact">
                <li><i class="fas fa-map-marker-alt"></i> 123 Đường Thời Trang, Quận 1, TP.HCM</li>
                <li><i class="fas fa-phone-alt"></i> 1900 1234</li>
                <li><i class="fas fa-envelope"></i> hello@loopfit.vn</li>
            </ul>
        </div>
    </div>

    <div class="footer__bottom">
        <p>&copy; <?= date('Y') ?> LoopFit - Thuê quần áo thông minh. All rights reserved.</p>
        <div class="footer__payment">
            <span>Chấp nhận thanh toán:</span>
            <i class="fab fa-cc-visa"></i>
            <i class="fab fa-cc-mastercard"></i>
            <i class="fab fa-cc-paypal"></i>
            <i class="fas fa-qrcode"></i>
        </div>
    </div>
</footer>