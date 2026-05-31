<script>
(function () {
    window.collectionGradeCodes = @json(\App\Models\CollectionItem::GRADE_CODES);
    window.collectionGradeLabels = @json(\App\Models\CollectionItem::GRADES);

    function rebuildCopyGrades(container, quantity) {
        var prefix = container.getAttribute('data-name-prefix') || 'copy_conditions';
        var plateLabel = container.getAttribute('data-plate-label') || 'plate';
        var compact = container.classList.contains('collection-copy-grades--compact');
        var existingSelect = container.querySelector('.collection-copy-grade-select');
        var inputClass = existingSelect
            ? existingSelect.className.replace('collection-copy-grade-select', '').trim()
            : '';
        var oldValues = [];

        container.querySelectorAll('.collection-copy-grade-select').forEach(function (select) {
            oldValues.push(select.value);
        });

        quantity = Math.max(0, parseInt(quantity, 10) || 0);
        container.innerHTML = '';

        if (quantity <= 0) {
            container.hidden = true;
            return;
        }

        container.hidden = false;

        for (var index = 0; index < quantity; index += 1) {
            var label = document.createElement('label');
            label.className = 'collection-copy-grade';

            var labelText = document.createElement('span');
            labelText.className = 'collection-copy-grade-label';
            labelText.textContent = compact ? ('#' + (index + 1)) : ('Copy ' + (index + 1));

            var select = document.createElement('select');
            select.name = prefix + '[' + index + ']';
            select.className = 'collection-copy-grade-select' + (inputClass ? ' ' + inputClass : '');
            select.setAttribute('aria-label', 'Grade for copy ' + (index + 1) + ' of ' + plateLabel);

            var emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = '—';
            select.appendChild(emptyOption);

            (window.collectionGradeCodes || []).forEach(function (code) {
                var option = document.createElement('option');
                option.value = code;
                option.textContent = compact
                    ? code
                    : ((window.collectionGradeLabels && window.collectionGradeLabels[code]) || code);
                if (oldValues[index] === code) {
                    option.selected = true;
                }
                select.appendChild(option);
            });

            if (oldValues[index] && !select.value) {
                select.value = oldValues[index];
            }

            label.appendChild(labelText);
            label.appendChild(select);
            container.appendChild(label);

            select.addEventListener('change', function () {
                container.dispatchEvent(new CustomEvent('collection-copy-grades-changed', { bubbles: true }));
            });
        }
    }

    document.querySelectorAll('[data-copy-grades]').forEach(function (container) {
        var row = container.closest('[data-collection-row]');
        var qtyInput = row
            ? row.querySelector('.collection-manage-qty')
            : container.closest('form')?.querySelector('[data-copy-qty-source]');

        if (qtyInput) {
            var sync = function () {
                rebuildCopyGrades(container, qtyInput.value);
                container.dispatchEvent(new CustomEvent('collection-copy-grades-changed', { bubbles: true }));
            };
            qtyInput.addEventListener('change', sync);
            qtyInput.addEventListener('input', sync);
        }
    });

    window.collectionRebuildCopyGrades = rebuildCopyGrades;
})();
</script>
