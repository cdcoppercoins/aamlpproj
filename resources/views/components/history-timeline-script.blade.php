<script>
(function () {
    var items = document.querySelectorAll('.history-accordion-item');
    if (!items.length) {
        return;
    }

    items.forEach(function (details) {
        details.addEventListener('toggle', function () {
            if (!details.open) {
                return;
            }

            var title = details.querySelector('.history-accordion-title');
            if (!title) {
                return;
            }

            // Wait for the previous panel to collapse before scrolling.
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    title.scrollIntoView({ block: 'start' });
                });
            });
        });
    });
})();
</script>
