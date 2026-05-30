<script>
(function () {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImg');
    const closeBtn = document.querySelector('.modal-close');
    const useTapFlip = window.matchMedia('(hover: none) and (pointer: coarse)').matches;

    const preloadedBacks = new Set();
    let flippedImg = null;

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

            if (!modal || !modalImg || !closeBtn) {
                return;
            }

            modal.style.display = 'flex';
            let src = this.dataset.original || this.src;
            src = src.replace(/(_[ab])(\.[^.]+)$/i, '_a$2').replace(/([ab])(\.[^.]+)$/i, 'a$2');
            modalImg.src = src;
        });
    });

    if (!modal || !modalImg || !closeBtn) {
        return;
    }

    function closeModal() {
        modal.style.display = 'none';
        modalImg.src = '';
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
