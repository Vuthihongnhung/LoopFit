// assets/user/js/home.js
$(document).ready(function() {
    // Slider banner
    const sliderTrack = document.getElementById('sliderTrack');
    if (!sliderTrack) return;

    const slides = document.querySelectorAll('.slide');
    const totalSlides = slides.length;
    if (totalSlides === 0) return;

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

    // Xử lý sao chép mã giảm giá (chỉ chạy nếu có phần tử)
    const copyButtons = document.querySelectorAll('.copy-code-btn');
    if (copyButtons.length) {
        copyButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const code = this.getAttribute('data-code');
                if (code) {
                    navigator.clipboard.writeText(code).then(() => {
                        if (typeof Toast !== 'undefined') {
                            Toast.success('Đã sao chép mã: ' + code, 'Thành công');
                        } else {
                            alert('Đã sao chép: ' + code);
                        }
                    }).catch(() => {
                        if (typeof Toast !== 'undefined') {
                            Toast.error('Không thể sao chép', 'Lỗi');
                        } else {
                            alert('Không thể sao chép');
                        }
                    });
                }
            });
        });
    }
});