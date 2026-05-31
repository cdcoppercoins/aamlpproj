<script>
(function () {
    document.querySelectorAll('[data-sortable-report]').forEach(function (table) {
        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        var buttons = table.querySelectorAll('.collection-report-sort-btn');
        var activeButton = null;
        var activeDirection = 'asc';

        function compareRows(a, b, key, type, direction) {
            var aVal = a.getAttribute('data-sort-' + key) || '';
            var bVal = b.getAttribute('data-sort-' + key) || '';

            if (type === 'number') {
                var aNum = parseFloat(aVal) || 0;
                var bNum = parseFloat(bVal) || 0;
                return direction === 'asc' ? aNum - bNum : bNum - aNum;
            }

            aVal = aVal.toLowerCase();
            bVal = bVal.toLowerCase();
            var result = aVal.localeCompare(bVal, undefined, { sensitivity: 'base', numeric: true });
            return direction === 'asc' ? result : -result;
        }

        function renumberRows() {
            tbody.querySelectorAll('tr').forEach(function (row, index) {
                var cell = row.querySelector('.collection-report-row-num');
                if (cell) {
                    cell.textContent = String(index + 1);
                }
            });
        }

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var key = button.getAttribute('data-sort-key');
                var type = button.getAttribute('data-sort-type') || 'text';
                if (!key) return;

                if (activeButton === button) {
                    activeDirection = activeDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    activeButton = button;
                    activeDirection = 'asc';
                }

                buttons.forEach(function (btn) {
                    btn.classList.remove('is-sorted-asc', 'is-sorted-desc');
                });
                button.classList.add(activeDirection === 'asc' ? 'is-sorted-asc' : 'is-sorted-desc');

                var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
                rows.sort(function (a, b) {
                    return compareRows(a, b, key, type, activeDirection);
                });

                rows.forEach(function (row) {
                    tbody.appendChild(row);
                });

                renumberRows();
            });
        });
    });

    document.querySelectorAll('[data-report-scope]').forEach(function (form) {
        var scopeInputs = form.querySelectorAll('input[name="scope"]');
        var panels = form.querySelectorAll('[data-report-scope-panel]');

        function syncPanels() {
            var selected = form.querySelector('input[name="scope"]:checked');
            var scope = selected ? selected.value : 'all';

            panels.forEach(function (panel) {
                var matches = panel.getAttribute('data-report-scope-panel') === scope;
                panel.hidden = !matches;
                panel.querySelectorAll('select').forEach(function (select) {
                    select.disabled = !matches;
                    select.required = matches;
                });
            });
        }

        scopeInputs.forEach(function (input) {
            input.addEventListener('change', syncPanels);
        });

        syncPanels();
    });
})();
</script>
