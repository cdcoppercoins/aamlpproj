<script>
(function () {
    var panel = document.getElementById('collection-listing-want-matches');
    if (!panel) {
        return;
    }

    var bodyEl = panel.querySelector('[data-want-matches-body]');
    var plateId = panel.getAttribute('data-plate-id');
    var url = panel.getAttribute('data-want-matches-url');
    var cache = null;

    function hasListedSelection() {
        var listed = false;
        document.querySelectorAll('[data-listing-type-select]').forEach(function (select) {
            if (select.value) {
                listed = true;
            }
        });
        return listed;
    }

    function selectedValues() {
        var values = [];
        document.querySelectorAll('[data-listing-type-select]').forEach(function (select) {
            if (select.value) {
                values.push(select.value);
            }
        });
        return values;
    }

    function renderMatches(data) {
        if (!bodyEl) {
            return;
        }

        if (!data || data.count === 0) {
            bodyEl.textContent = 'No other members have this plate on their want list right now.';
            panel.hidden = false;
            return;
        }

        var names = data.matches.map(function (match) {
            return match.username;
        }).join(', ');

        bodyEl.textContent = data.count + ' member' + (data.count === 1 ? '' : 's') + ' want this plate: ' + names + '.';
        panel.hidden = false;
    }

    function fetchMatches() {
        if (!url || !plateId) {
            return;
        }

        if (cache) {
            renderMatches(cache);
            return;
        }

        var requestUrl = url + (url.indexOf('?') >= 0 ? '&' : '?') + 'plate_id=' + encodeURIComponent(plateId);

        fetch(requestUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                cache = data;
                renderMatches(data);
            })
            .catch(function () {
                if (bodyEl) {
                    bodyEl.textContent = 'Could not load want list matches.';
                    panel.hidden = false;
                }
            });
    }

    function syncPanel() {
        if (!hasListedSelection()) {
            panel.hidden = true;
            return;
        }

        fetchMatches();
    }

    document.addEventListener('change', function (event) {
        if (event.target && event.target.matches('[data-listing-type-select]')) {
            syncPanel();
        }
    });

    syncPanel();
})();
</script>
