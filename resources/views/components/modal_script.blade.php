<script>
(function () {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImg');
    const closeBtn = document.querySelector('.modal-close');

    const preloadedBacks = new Set();
    document.querySelectorAll('.thumb-img[data-hover]').forEach(function (img) {
        const hoverSrc = img.dataset.hover;
        if (!hoverSrc || preloadedBacks.has(hoverSrc)) {
            return;
        }
        preloadedBacks.add(hoverSrc);
        const preload = new Image();
        preload.decoding = 'async';
        preload.src = hoverSrc;
    });

    if (!modal || !modalImg || !closeBtn) return;

    document.querySelectorAll('.thumb-img').forEach(function (img) {
        img.addEventListener('click', function () {
            modal.style.display = 'flex';
            let src = this.dataset.original || this.src;
            src = src.replace(/(_[ab])(\.[^.]+)$/i, '_a$2').replace(/([ab])(\.[^.]+)$/i, 'a$2');
            modalImg.src = src;
        });
    });

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
