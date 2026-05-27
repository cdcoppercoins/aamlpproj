<script>
(function () {
    var modal = document.getElementById('historyModal');
    var modalTitle = document.getElementById('historyModalTitle');
    var modalBody = document.getElementById('historyModalBody');
    var modalMedia = document.getElementById('historyModalMedia');
    var modalImg = document.getElementById('historyModalImg');
    var modalCaption = document.getElementById('historyModalCaption');
    var modalScroll = modal ? modal.querySelector('.history-modal-scroll') : null;
    var modalLayout = document.getElementById('historyModalLayout');
    var placeholder = document.getElementById('historyPreviewPlaceholder');
    var markers = document.querySelectorAll('.history-timeline-marker');
    var activeMarker = null;

    if (!modal || !markers.length) {
        return;
    }

    function setActiveMarker(marker) {
        markers.forEach(function (btn) {
            var isActive = btn === marker;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-expanded', isActive ? 'true' : 'false');
        });
        activeMarker = marker;
    }

    function captionTextFor(id) {
        if (!id) {
            return '';
        }
        var source = document.getElementById('history-caption-' + id);
        return source ? source.textContent.trim() : '';
    }

    function setCaption(text) {
        if (!modalCaption) {
            return false;
        }
        if (text) {
            modalCaption.textContent = text;
            modalCaption.hidden = false;
            return true;
        }
        modalCaption.textContent = '';
        modalCaption.hidden = true;
        return false;
    }

    function hideImage() {
        if (!modalImg) {
            return;
        }
        modalImg.style.display = 'none';
        modalImg.removeAttribute('src');
        modalImg.alt = '';
    }

    function showImage(src, alt) {
        if (!modalImg) {
            return;
        }
        modalImg.style.display = '';
        modalImg.alt = alt || '';
        modalImg.onerror = function () {
            hideImage();
        };
        modalImg.src = src;
    }

    function openModal(marker) {
        setActiveMarker(marker);

        var id = marker.getAttribute('data-history-id') || '';
        var title = marker.getAttribute('data-history-title') || '';
        var image = marker.getAttribute('data-history-image') || '';
        var alt = marker.getAttribute('data-history-alt') || title;
        var caption = captionTextFor(id);
        var source = id ? document.getElementById('history-content-' + id) : null;
        var hasImage = Boolean(image);
        var hasCaption = Boolean(caption);

        modalTitle.textContent = title;
        modalBody.innerHTML = source ? source.innerHTML : '';

        if (hasImage || hasCaption) {
            if (modalMedia) {
                modalMedia.hidden = false;
            }
            if (hasImage) {
                showImage(image, alt);
            } else {
                hideImage();
            }
            setCaption(hasCaption ? caption : '');
        } else if (modalMedia) {
            hideImage();
            setCaption('');
            modalMedia.hidden = true;
        }

        if (modalLayout) {
            modalLayout.classList.toggle('has-media', hasImage || hasCaption);
        }

        modal.hidden = false;
        modal.classList.add('is-open');
        if (placeholder) {
            placeholder.hidden = true;
        }
        if (modalScroll) {
            modalScroll.scrollTop = 0;
        }
        document.body.classList.add('history-modal-open');
    }

    function closeModal() {
        modal.hidden = true;
        modal.classList.remove('is-open');
        if (placeholder) {
            placeholder.hidden = false;
        }
        document.body.classList.remove('history-modal-open');
        hideImage();
        setCaption('');
        if (modalMedia) {
            modalMedia.hidden = true;
        }
        if (modalLayout) {
            modalLayout.classList.remove('has-media');
        }
        markers.forEach(function (btn) {
            btn.classList.remove('is-active');
            btn.setAttribute('aria-expanded', 'false');
        });
        activeMarker = null;
    }

    markers.forEach(function (marker) {
        marker.addEventListener('click', function () {
            openModal(marker);
        });
        marker.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openModal(marker);
            }
        });
    });

    modal.querySelectorAll('[data-history-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });
})();
</script>
