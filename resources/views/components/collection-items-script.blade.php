<script>
(function () {
    window.collectionGradeCodes = @json(\App\Models\CollectionItem::GRADE_CODES);
    window.collectionGradeLabels = @json(\App\Models\CollectionItem::GRADES);

    function itemRowHtml(container, index) {
        var prefix = container.getAttribute('data-name-prefix') || 'owned_items';
        var plateLabel = container.getAttribute('data-plate-label') || 'plate';
        var compact = container.getAttribute('data-compact') === '1';
        var existingSelect = container.querySelector('.collection-item-grade');
        var inputClass = existingSelect
            ? existingSelect.className.replace('collection-item-grade', '').trim()
            : '';

        var row = document.createElement('div');
        row.className = 'collection-item-row' + (compact ? ' collection-item-row--compact' : '');
        row.setAttribute('data-item-row', '');

        var gradeSelect = document.createElement('select');
        gradeSelect.name = prefix + '[' + index + '][grade]';
        gradeSelect.className = 'collection-item-grade' + (inputClass ? ' ' + inputClass : '');
        gradeSelect.setAttribute('aria-label', 'Grade for item ' + (index + 1) + ' of ' + plateLabel);

        var emptyOption = document.createElement('option');
        emptyOption.value = '';
        emptyOption.textContent = '—';
        gradeSelect.appendChild(emptyOption);

        (window.collectionGradeCodes || []).forEach(function (code) {
            var option = document.createElement('option');
            option.value = code;
            option.textContent = compact
                ? code
                : ((window.collectionGradeLabels && window.collectionGradeLabels[code]) || code);
            gradeSelect.appendChild(option);
        });

        var serialDisplay = document.createElement('span');
        serialDisplay.className = 'collection-item-serial-display';
        serialDisplay.textContent = '—';
        serialDisplay.setAttribute('aria-label', 'Serial number for item ' + (index + 1) + ' of ' + plateLabel);

        if (compact) {
            var indexLabel = document.createElement('span');
            indexLabel.className = 'collection-item-row-label';
            indexLabel.textContent = '#' + (index + 1);

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'collection-item-remove-btn';
            removeBtn.setAttribute('data-remove-item', '');
            removeBtn.textContent = '×';
            removeBtn.setAttribute('aria-label', 'Remove item ' + (index + 1));

            row.appendChild(indexLabel);
            row.appendChild(gradeSelect);
            row.appendChild(serialDisplay);
            row.appendChild(removeBtn);
        } else {
            var head = document.createElement('div');
            head.className = 'collection-item-row-head';

            var label = document.createElement('span');
            label.className = 'collection-item-row-label';
            label.textContent = 'Item ' + (index + 1);

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'collection-item-remove-btn';
            removeBtn.setAttribute('data-remove-item', '');
            removeBtn.setAttribute('aria-label', 'Remove item ' + (index + 1) + ' of ' + plateLabel);
            removeBtn.textContent = 'Remove';

            head.appendChild(label);
            head.appendChild(removeBtn);

            var fields = document.createElement('div');
            fields.className = 'collection-item-row-fields';

            var gradeLabel = document.createElement('label');
            gradeLabel.className = 'collection-item-field';
            var gradeText = document.createElement('span');
            gradeText.className = 'collection-item-field-label';
            gradeText.textContent = 'Grade';
            gradeLabel.appendChild(gradeText);
            gradeLabel.appendChild(gradeSelect);

            var serialField = document.createElement('div');
            serialField.className = 'collection-item-field';
            var serialText = document.createElement('span');
            serialText.className = 'collection-item-field-label';
            serialText.textContent = 'Serial #';
            serialField.appendChild(serialText);
            serialField.appendChild(serialDisplay);
            serialDisplay.textContent = 'Assigned when saved';

            fields.appendChild(gradeLabel);
            fields.appendChild(serialField);
            row.appendChild(head);
            row.appendChild(fields);
        }

        return row;
    }

    function reindexItems(container) {
        var prefix = container.getAttribute('data-name-prefix') || 'owned_items';
        var plateLabel = container.getAttribute('data-plate-label') || 'plate';
        var compact = container.getAttribute('data-compact') === '1';
        var list = container.querySelector('[data-items-list]');
        if (!list) return;

        list.querySelectorAll('[data-item-row]').forEach(function (row, index) {
            var label = row.querySelector('.collection-item-row-label');
            if (label) {
                label.textContent = compact ? ('#' + (index + 1)) : ('Item ' + (index + 1));
            }

            row.querySelectorAll('[name]').forEach(function (input) {
                var match = input.name.match(/\[\d+\]\[([^\]]+)\]$/);
                if (!match) return;
                input.name = prefix + '[' + index + '][' + match[1] + ']';
            });

            var grade = row.querySelector('.collection-item-grade');
            if (grade) {
                grade.setAttribute('aria-label', 'Grade for item ' + (index + 1) + ' of ' + plateLabel);
            }

            var serial = row.querySelector('.collection-item-serial-display');
            if (serial) {
                serial.setAttribute('aria-label', 'Serial number for item ' + (index + 1) + ' of ' + plateLabel);
            }

            var removeBtn = row.querySelector('[data-remove-item]');
            if (removeBtn) {
                removeBtn.setAttribute('aria-label', 'Remove item ' + (index + 1) + ' of ' + plateLabel);
            }
        });

        container.dispatchEvent(new CustomEvent('collection-items-changed', { bubbles: true }));
    }

    function bindItemRow(container, row) {
        var grade = row.querySelector('.collection-item-grade');
        if (grade) {
            grade.addEventListener('change', function () {
                container.dispatchEvent(new CustomEvent('collection-items-changed', { bubbles: true }));
            });
        }

        var removeBtn = row.querySelector('[data-remove-item]');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                row.remove();
                reindexItems(container);
            });
        }
    }

    document.querySelectorAll('[data-collection-items]').forEach(function (container) {
        var list = container.querySelector('[data-items-list]');
        var addBtn = container.querySelector('[data-add-item]');
        if (!list || !addBtn) return;

        list.querySelectorAll('[data-item-row]').forEach(function (row) {
            bindItemRow(container, row);
        });

        addBtn.addEventListener('click', function () {
            var index = list.querySelectorAll('[data-item-row]').length;
            var row = itemRowHtml(container, index);
            list.appendChild(row);
            bindItemRow(container, row);
            reindexItems(container);
        });
    });

    window.collectionReplaceItems = function (container, count, grade) {
        var list = container.querySelector('[data-items-list]');
        if (!list) return;

        list.innerHTML = '';
        count = Math.max(0, parseInt(count, 10) || 0);

        for (var index = 0; index < count; index += 1) {
            var row = itemRowHtml(container, index);
            list.appendChild(row);
            bindItemRow(container, row);
            var gradeSelect = row.querySelector('.collection-item-grade');
            if (gradeSelect && grade) {
                gradeSelect.value = grade;
            }
        }

        reindexItems(container);
    };
})();
</script>
