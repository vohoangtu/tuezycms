// TinyMCE Configuration with Media Picker Integration

let mediaPickerCallback = null;

function initTinyMCE(selector, options = {}) {
    const defaultOptions = {
        selector: selector,
        height: 500,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | link image media | code | help',
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
        image_advtab: true,
        file_picker_types: 'image',
        file_picker_callback: function(callback, value, meta) {
            // Open media library when user clicks image/media button
            if (meta.filetype === 'image' || meta.filetype === 'media') {
                openMediaPickerForEditor(callback, meta);
            }
        },
        setup: function(editor) {
            // Add custom media button
            editor.ui.registry.addButton('mediapicker', {
                text: 'Media Library',
                tooltip: 'Insert from Media Library',
                onAction: function() {
                    openMediaPickerForEditor(function(url, meta) {
                        if (meta.filetype === 'image') {
                            editor.insertContent('<img src="' + url + '" alt="" />');
                        } else {
                            editor.insertContent('<a href="' + url + '">' + url + '</a>');
                        }
                    }, meta);
                }
            });
        },
        ...options
    };

    return tinymce.init(defaultOptions);
}

function openMediaPickerForEditor(callback, meta) {
    const width = 900;
    const height = 700;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;

    // Store callback for media picker
    window.editorMediaCallback = callback;
    window.editorMediaMeta = meta;

    // Open media library in popup
    const popup = window.open(
        '/admin/media?mode=picker',
        'mediaPicker',
        `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`
    );

    // Handle media selection
    window.onEditorMediaSelected = function(mediaId, mediaUrl, thumbnailUrl) {
        if (window.editorMediaCallback) {
            const meta = window.editorMediaMeta || { filetype: 'image' };
            
            if (meta.filetype === 'image') {
                // Insert image with thumbnail URL
                window.editorMediaCallback(thumbnailUrl || mediaUrl, {
                    title: '',
                    alt: '',
                    width: '',
                    height: ''
                });
            } else {
                // Insert link or media
                window.editorMediaCallback(mediaUrl, {
                    text: mediaUrl
                });
            }
        }
        
        if (popup) {
            popup.close();
        }
        
        // Cleanup
        delete window.editorMediaCallback;
        delete window.editorMediaMeta;
        delete window.onEditorMediaSelected;
    };
}

// Initialize TinyMCE when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Wait for TinyMCE to be loaded
    if (typeof tinymce === 'undefined') {
        console.warn('TinyMCE not loaded. Make sure to include TinyMCE script before this file.');
        return;
    }
    
    // Auto-initialize editors with class 'wysiwyg-editor'
    const editors = document.querySelectorAll('.wysiwyg-editor');
    editors.forEach(function(textarea) {
        if (textarea.id && !tinymce.get(textarea.id)) {
            initTinyMCE('#' + textarea.id);
        }
    });
});

