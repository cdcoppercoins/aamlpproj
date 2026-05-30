<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js"></script>
<script>
(function () {
    var textarea = document.getElementById('page-body-editor');
    if (!textarea || typeof tinymce === 'undefined') {
        return;
    }

    var uploadUrl = @json(route('admin.pages.upload-image'));
    var csrfToken = @json(csrf_token());

    tinymce.init({
        selector: '#page-body-editor',
        license_key: 'gpl',
        promotion: false,
        height: 496,
        min_height: 496,
        max_height: 496,
        menubar: false,
        branding: false,
        plugins: 'link lists image code table',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | link image | removeformat code',
        block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3',
        image_advtab: true,
        image_title: true,
        image_description: true,
        object_resizing: 'img',
        resize_img_proportional: true,
        automatic_uploads: true,
        paste_data_images: true,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        content_style: 'body { font-family: Literata, Georgia, serif; font-size: 16px; line-height: 1.55; color: #222; } '
            + 'img { max-width: 100%; height: auto; } '
            + 'img.align-left, img[style*="float: left"], img.static-page-img-left { float: left; margin: 0 1em 1em 0; } '
            + 'img.align-right, img[style*="float: right"], img.static-page-img-right { float: right; margin: 0 0 1em 1em; } '
            + 'p { margin: 0 0 1em; }',
        image_class_list: [
            { title: 'Default', value: '' },
            { title: 'Float left (text wraps)', value: 'static-page-img-left' },
            { title: 'Float right (text wraps)', value: 'static-page-img-right' },
        ],
        images_upload_handler: function (blobInfo, progress) {
            return new Promise(function (resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', uploadUrl);
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.upload.onprogress = function (event) {
                    if (event.lengthComputable) {
                        progress(event.loaded / event.total * 100);
                    }
                };
                xhr.onload = function () {
                    if (xhr.status < 200 || xhr.status >= 300) {
                        reject('Image upload failed.');
                        return;
                    }

                    var json = JSON.parse(xhr.responseText);
                    if (!json || !json.location) {
                        reject('Invalid upload response.');
                        return;
                    }

                    resolve(json.location);
                };
                xhr.onerror = function () {
                    reject('Image upload failed.');
                };

                var formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                xhr.send(formData);
            });
        },
        setup: function (editor) {
            editor.on('change keyup', function () {
                textarea.value = editor.getContent();
            });
        },
    });

    var form = textarea.closest('form');
    if (form) {
        form.addEventListener('submit', function () {
            if (tinymce.get('page-body-editor')) {
                textarea.value = tinymce.get('page-body-editor').getContent();
            }
        });
    }
})();
</script>
