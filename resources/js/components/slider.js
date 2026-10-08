export function initSlider() {
    const slider = document.querySelector('[data-hero-slider]');
    if (!slider) return;

    const track = slider.querySelector('[data-slider-track]');
    const slides = slider.querySelectorAll('[data-slide-index]');
    const prevBtn = slider.querySelector('[data-slider-prev]');
    const nextBtn = slider.querySelector('[data-slider-next]');
    const dots = slider.querySelectorAll('[data-slide]');

    const totalSlides = slides.length;
    if (totalSlides <= 1) return;

    let currentIndex = 0;
    let autoPlayTimer = null;
    const AUTO_DELAY = 5000;

    function goToSlide(index) {
        currentIndex = (index % totalSlides + totalSlides) % totalSlides;

        if (track) {
            track.style.transform = `translateX(-${currentIndex * 100}%)`;
        }

        slides.forEach((slide, idx) => {
            const isActive = idx === currentIndex;
            slide.classList.toggle('hero__slide--active', isActive);
            slide.setAttribute('aria-hidden', (!isActive).toString());
        });

        dots.forEach((dot, idx) => {
            const isActive = idx === currentIndex;
            dot.classList.toggle('hero__dot--active', isActive);
            dot.setAttribute('aria-selected', isActive.toString());
        });
    }

    function startAutoPlay() {
        stopAutoPlay();
        autoPlayTimer = setInterval(() => {
            goToSlide(currentIndex + 1);
        }, AUTO_DELAY);
    }

    function stopAutoPlay() {
        if (autoPlayTimer) {
            clearInterval(autoPlayTimer);
            autoPlayTimer = null;
        }
    }

    function resetAutoPlay() {
        stopAutoPlay();
        startAutoPlay();
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            goToSlide(currentIndex - 1);
            resetAutoPlay();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            goToSlide(currentIndex + 1);
            resetAutoPlay();
        });
    }

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            goToSlide(index);
            resetAutoPlay();
        });
    });

    slider.addEventListener('mouseenter', stopAutoPlay);
    slider.addEventListener('mouseleave', startAutoPlay);

    // Keyboard support
    slider.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            goToSlide(currentIndex - 1);
            resetAutoPlay();
        } else if (e.key === 'ArrowRight') {
            goToSlide(currentIndex + 1);
            resetAutoPlay();
        }
    });

    // Touch swipe support for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    slider.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        stopAutoPlay();
    }, { passive: true });

    slider.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        const diff = touchStartX - touchEndX;
        if (Math.abs(diff) > 40) {
            if (diff > 0) {
                goToSlide(currentIndex + 1);
            } else {
                goToSlide(currentIndex - 1);
            }
        }
        startAutoPlay();
    }, { passive: true });

    // Initialize first slide and start auto timer
    goToSlide(0);
    startAutoPlay();
}
