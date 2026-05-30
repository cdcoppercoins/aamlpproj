<script>
(function () {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImg');
    const closeBtn = document.querySelector('.modal-close');
    const useTapFlip = window.matchMedia('(hover: none) and (pointer: coarse)').matches;

    const preloadedBacks = new Set();
    let flippedImg = null;

    const zoomIcon = '<svg class="thumb-img-zoom-icon" viewBox="0 0 24 24" aria-hidden="true">'
        + '<path d="M9 3H3v6M15 3h6v6M3 15v6h6M21 15v6h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>'
        + '</svg>';

    function preloadBackImage(url) {
        if (!url || preloadedBacks.has(url)) {
            return;
        }
        preloadedBacks.add(url);
        const preload = new Image();
        preload.src = url;
        if (typeof preload.decode === 'function') {
            preload.decode().catch(function () {});
        }
    }

    document.querySelectorAll('.thumb-img[data-hover]').forEach(function (img) {
        preloadBackImage(img.dataset.hover);
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
            flippedImg = img;
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

    document.querySelectorAll('.thumb-img[data-hover]').forEach(function (img) {
        if (useTapFlip) {
            img.dataset.showingBack = '0';
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

        document.addEventListener('click', function () {
            resetFlipped();
        });
    }

    document.querySelectorAll('.thumb-img').forEach(function (img) {
        img.addEventListener('click', function (e) {
            if (useTapFlip && this.dataset.hover) {
                e.preventDefault();
                e.stopPropagation();
                tapShowBack(this);
                return;
            }

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
        if (e.target === modal) closeModal();
    });
    modalImg.addEventListener('click', closeModal);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
    });
})();
</script>
