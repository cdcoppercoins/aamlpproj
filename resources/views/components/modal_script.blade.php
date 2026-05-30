<script>
(function () {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImg');
    const closeBtn = document.querySelector('.modal-close');
    const useTapFlip = window.matchMedia('(hover: none) and (pointer: coarse)').matches;

    const preloadedUrls = new Set();
    let flippedImg = null;

    const zoomIcon = '<svg class="thumb-img-zoom-icon" viewBox="0 0 24 24" aria-hidden="true">'
        + '<path d="M9 3H3v6M15 3h6v6M3 15v6h6M21 15v6h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>'
        + '</svg>';

    function preloadImage(url) {
        if (!url || preloadedUrls.has(url)) {
            return;
        }

        preloadedUrls.add(url);
        const preload = new Image();
        preload.src = url;
        if (typeof preload.decode === 'function') {
            preload.decode().catch(function () {});
        }
    }

    document.querySelectorAll('.thumb-img').forEach(function (img) {
        preloadImage(img.getAttribute('src'));
        preloadImage(img.dataset.original);
        preloadImage(img.dataset.hover);
    });

    function frontImageSrc(img) {
        let src = img.dataset.original || img.src;
        return src.replace(/(_[ab])(\.[^.]+)$/i, '_a$2').replace(/([ab])(\.[^.]+)$/i, 'a$2');
    }

    function showFront(img) {
        img.src = img.dataset.original || img.src;
        img.dataset.showingBack = '0';
        if (flippedImg === img) {
            flippedImg = null;
        }
    }

    function showBack(img) {
        if (!img.dataset.hover) {
            return;
        }

        img.src = img.dataset.hover;
        img.dataset.showingBack = '1';
        flippedImg = img;
    }

    function resetFlipped() {
        if (flippedImg) {
            showFront(flippedImg);
        }
    }

    function tapShowBack(img) {
        if (flippedImg && flippedImg !== img) {
            showFront(flippedImg);
        }

        if (img.dataset.showingBack === '1') {
            return;
        }

        showBack(img);
    }

    function openModalFront(img) {
        if (!modal || !modalImg || !closeBtn) {
            return;
        }

        resetFlipped();
        if (img.dataset.original) {
            showFront(img);
        }

        modal.style.display = 'flex';
        modalImg.src = frontImageSrc(img);
        modalImg.alt = img.alt || '';
    }

    function addTouchZoomButton(img) {
        const container = img.closest('.gallery-set-cell, .gallery-result-image-wrap');
        if (!container || container.querySelector(':scope > .thumb-img-zoom')) {
            return;
        }

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'thumb-img-zoom';
        btn.setAttribute('aria-label', 'View larger image');
        btn.innerHTML = zoomIcon;
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            openModalFront(img);
        });
        container.appendChild(btn);
    }

    function setupTouchFlip(img) {
        let touchStart = null;

        img.addEventListener('contextmenu', function (e) {
            e.preventDefault();
        });

        img.addEventListener('touchstart', function (e) {
            if (e.touches.length !== 1) {
                touchStart = null;
                return;
            }

            touchStart = {
                x: e.touches[0].clientX,
                y: e.touches[0].clientY,
                time: Date.now(),
            };
        }, { passive: true });

        img.addEventListener('touchmove', function (e) {
            if (!touchStart || e.touches.length !== 1) {
                return;
            }

            const dx = e.touches[0].clientX - touchStart.x;
            const dy = e.touches[0].clientY - touchStart.y;
            if (Math.hypot(dx, dy) > 12) {
                touchStart = null;
            }
        }, { passive: true });

        img.addEventListener('touchcancel', function () {
            touchStart = null;
        }, { passive: true });

        img.addEventListener('touchend', function (e) {
            if (!touchStart) {
                return;
            }

            const elapsed = Date.now() - touchStart.time;
            touchStart = null;

            if (elapsed > 450) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();
            tapShowBack(img);
        }, { passive: false });
    }

    function handleOutsideTap(e) {
        if (!flippedImg) {
            return;
        }

        if (e.target.closest('.thumb-img') === flippedImg) {
            return;
        }

        resetFlipped();
    }

    document.querySelectorAll('.thumb-img[data-hover]').forEach(function (img) {
        if (useTapFlip) {
            img.dataset.showingBack = '0';
            img.setAttribute('draggable', 'false');
            setupTouchFlip(img);
            return;
        }

        img.addEventListener('mouseenter', function () {
            if (this.dataset.hover) {
                showBack(this);
            }
        });

        img.addEventListener('mouseleave', function () {
            showFront(this);
        });
    });

    if (useTapFlip) {
        document.querySelectorAll('.thumb-img').forEach(addTouchZoomButton);

        document.addEventListener('touchend', handleOutsideTap, { passive: true });
        document.addEventListener('click', handleOutsideTap);
    }

    document.querySelectorAll('.thumb-img').forEach(function (img) {
        if (useTapFlip && img.dataset.hover) {
            return;
        }

        img.addEventListener('click', function () {
            openModalFront(this);
        });
    });

    if (!modal || !modalImg || !closeBtn) {
        return;
    }

    function closeModal() {
        modal.style.display = 'none';
        modalImg.src = '';
        modalImg.alt = '';
    }

    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    modalImg.addEventListener('click', closeModal);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeModal();
        }
    });
})();
</script>
