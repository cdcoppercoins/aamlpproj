<script>
(function () {
    var items = Array.prototype.slice.call(document.querySelectorAll('.history-accordion-item'));
    if (!items.length) {
        return;
    }

    var timeline = document.getElementById('history-timeline');
    var scrollOffset = 16;

    function scrollOpenItem(details) {
        var target = details.querySelector('summary') || details.querySelector('.history-accordion-title');
        if (!target) {
            return;
        }

        document.documentElement.style.overflowAnchor = 'none';

        function applyScroll() {
            var top = target.getBoundingClientRect().top + window.pageYOffset - scrollOffset;
            window.scrollTo(0, Math.max(0, top));
        }

        function finish() {
            applyScroll();
            window.requestAnimationFrame(function () {
                applyScroll();
                window.setTimeout(function () {
                    applyScroll();
                    document.documentElement.style.overflowAnchor = '';
                }, 80);
            });
        }

        if (timeline && typeof ResizeObserver !== 'undefined') {
            var settled = false;

            var observer = new ResizeObserver(function () {
                if (settled) {
                    return;
                }
                settled = true;
                observer.disconnect();
                finish();
            });

            observer.observe(timeline);

            window.setTimeout(function () {
                if (settled) {
                    return;
                }
                settled = true;
                observer.disconnect();
                finish();
            }, 150);
        } else {
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(finish);
            });
        }
    }

    items.forEach(function (details) {
        var summary = details.querySelector('summary');
        if (!summary) {
            return;
        }

        summary.addEventListener('click', function (event) {
            if (details.open) {
                return;
            }

            var previouslyOpen = null;
            for (var i = 0; i < items.length; i += 1) {
                if (items[i].open && items[i] !== details) {
                    previouslyOpen = items[i];
                    break;
                }
            }

            if (!previouslyOpen) {
                return;
            }

            event.preventDefault();
            previouslyOpen.open = false;
            details.open = true;
            scrollOpenItem(details);
        });

        details.addEventListener('toggle', function () {
            if (!details.open) {
                return;
            }

            for (var i = 0; i < items.length; i += 1) {
                if (items[i].open && items[i] !== details) {
                    return;
                }
            }

            scrollOpenItem(details);
        });
    });
})();
</script>
