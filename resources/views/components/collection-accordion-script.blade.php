<script>
(function () {
    document.querySelectorAll('.collection-reports-accordion-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            var panel = document.getElementById(trigger.getAttribute('aria-controls'));
            if (!panel) {
                return;
            }

            var isOpen = trigger.getAttribute('aria-expanded') === 'true';

            if (isOpen) {
                trigger.setAttribute('aria-expanded', 'false');
                panel.hidden = true;
            } else {
                trigger.setAttribute('aria-expanded', 'true');
                panel.hidden = false;
            }
        });
    });
})();
</script>
