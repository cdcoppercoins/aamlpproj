(function () {
    'use strict';

    var rotators = document.querySelectorAll('.home-hero-rotator');
    if (!rotators.length) {
        return;
    }

    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    rotators.forEach(function (rotator) {
        var slides = Array.prototype.slice.call(rotator.querySelectorAll('.home-hero-slide'));
        if (slides.length <= 1) {
            return;
        }

        var intervalMs = parseInt(rotator.getAttribute('data-interval'), 10) || 7000;
        var dots = Array.prototype.slice.call(rotator.querySelectorAll('.home-hero-rotator-dot'));
        var prevBtn = rotator.querySelector('.home-hero-rotator-prev');
        var nextBtn = rotator.querySelector('.home-hero-rotator-next');
        var liveRegion = rotator.querySelector('.home-hero-rotator-live');
        var current = 0;
        var timer = null;
        var paused = false;

        function announce(slide) {
            if (!liveRegion) {
                return;
            }
            var headline = slide.querySelector('.home-hero-slide-headline');
            liveRegion.textContent = headline ? headline.textContent : '';
        }

        function show(index) {
            current = (index + slides.length) % slides.length;

            slides.forEach(function (slide, i) {
                var active = i === current;
                slide.classList.toggle('is-active', active);
                slide.hidden = !active;
            });

            dots.forEach(function (dot, i) {
                var active = i === current;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            announce(slides[current]);
        }

        function next() {
            show(current + 1);
        }

        function prev() {
            show(current - 1);
        }

        function stopTimer() {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        }

        function startTimer() {
            stopTimer();
            if (reducedMotion || paused || slides.length <= 1) {
                return;
            }
            timer = window.setInterval(next, intervalMs);
        }

        function pause() {
            paused = true;
            stopTimer();
        }

        function resume() {
            paused = false;
            startTimer();
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                prev();
                startTimer();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                next();
                startTimer();
            });
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                var target = parseInt(dot.getAttribute('data-slide-to'), 10);
                if (!isNaN(target)) {
                    show(target);
                    startTimer();
                }
            });
        });

        rotator.addEventListener('mouseenter', pause);
        rotator.addEventListener('mouseleave', resume);
        rotator.addEventListener('focusin', pause);
        rotator.addEventListener('focusout', function (event) {
            if (!rotator.contains(event.relatedTarget)) {
                resume();
            }
        });

        rotator.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                prev();
                startTimer();
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                next();
                startTimer();
            }
        });

        show(0);
        startTimer();
    });
})();
