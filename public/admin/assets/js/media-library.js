// Media Library JavaScript

// Drag and drop upload
document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    const fileInput = document.getElementById('fileInput');
    const uploadModal = document.getElementById('uploadModal');
    
    if (uploadForm && fileInput && uploadModal) {
        // Drag and drop
        uploadModal.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            uploadModal.classList.add('drag-over');
        });
        
        uploadModal.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            uploadModal.classList.remove('drag-over');
        });
        
        uploadModal.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            uploadModal.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
            }
        });
    }
});

// Media picker functions for use in other pages
window.selectMediaFromLibrary = function(callback, pickerMode = false) {
    const width = 900;
    const height = 700;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;
    
    const url = pickerMode ? '/admin/media?mode=picker' : '/admin/media';
    window.onMediaSelected = callback;
    
    window.open(url, 'mediaPicker', `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`);
};

