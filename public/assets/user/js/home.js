// assets/user/js/home.js

$(document).ready(function() {
    // ========== BANNER SLIDER ==========
    const sliderTrack = document.getElementById('sliderTrack');
    if (sliderTrack) {
        const slides = document.querySelectorAll('.slide');
        const totalSlides = slides.length;
        if (totalSlides > 0) {
            let currentIndex = 0;
            let autoInterval;
            const autoDelay = 5000;

            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const dotsContainer = document.getElementById('sliderDots');

            function updateSlider() {
                const containerWidth = document.querySelector('.slider-container').offsetWidth;
                sliderTrack.style.transform = `translateX(-${currentIndex * containerWidth}px)`;
                if (dotsContainer) {
                    const dots = dotsContainer.querySelectorAll('.dot');
                    dots.forEach((dot, idx) => {
                        dot.classList.toggle('active', idx === currentIndex);
                    });
                }
            }

            function nextSlide() {
                currentIndex = (currentIndex + 1) % totalSlides;
                updateSlider();
            }

            function prevSlide() {
                currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
                updateSlider();
            }

            function createDots() {
                if (!dotsContainer) return;
                dotsContainer.innerHTML = '';
                for (let i = 0; i < totalSlides; i++) {
                    const dot = document.createElement('div');
                    dot.classList.add('dot');
                    if (i === currentIndex) dot.classList.add('active');
                    dot.setAttribute('data-index', i);
                    dot.addEventListener('click', () => {
                        stopAuto();
                        currentIndex = i;
                        updateSlider();
                        startAuto();
                    });
                    dotsContainer.appendChild(dot);
                }
            }

            function startAuto() {
                if (autoInterval) clearInterval(autoInterval);
                autoInterval = setInterval(nextSlide, autoDelay);
            }

            function stopAuto() {
                if (autoInterval) clearInterval(autoInterval);
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    stopAuto();
                    prevSlide();
                    startAuto();
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    stopAuto();
                    nextSlide();
                    startAuto();
                });
            }

            const sliderContainer = document.querySelector('.banner-slider');
            if (sliderContainer) {
                sliderContainer.addEventListener('mouseenter', stopAuto);
                sliderContainer.addEventListener('mouseleave', startAuto);
            }

            window.addEventListener('resize', updateSlider);

            createDots();
            updateSlider();
            startAuto();
        }
    }

    // ========== SAO CHÉP MÃ VOUCHER (voucher thường) ==========
    const copyButtons = document.querySelectorAll('.copy-code-btn');
    if (copyButtons.length) {
        copyButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const code = this.getAttribute('data-code');
                if (code) {
                    navigator.clipboard.writeText(code).then(() => {
                        Toast.success('Đã sao chép mã: ' + code, 'Thành công');
                    }).catch(() => {
                        Toast.error('Không thể sao chép mã', 'Lỗi');
                    });
                }
            });
        });
    }

    // ========== POPUP VOUCHER ==========
    const popup = document.getElementById('voucher-popup');
    if (popup) {
        if (!sessionStorage.getItem('popupSeen')) {
            setTimeout(() => {
                popup.style.display = 'flex';
            }, 1000);
            sessionStorage.setItem('popupSeen', 'true');
        }

        const closeBtn = popup.querySelector('.popup-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                popup.style.display = 'none';
            });
        }

        popup.addEventListener('click', (e) => {
            if (e.target === popup) popup.style.display = 'none';
        });

        const copyBtn = popup.querySelector('.btn-copy');
        if (copyBtn) {
            copyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const code = copyBtn.getAttribute('data-code') || 'CHAOHÉ50';
                navigator.clipboard.writeText(code).then(() => {
                    Toast.success('Đã sao chép mã: ' + code, 'Thành công');
                }).catch(() => {
                    Toast.error('Không thể sao chép, vui lòng thử lại', 'Lỗi');
                });
            });
        }
    }
});