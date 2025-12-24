<?php
// View for media library page - Data prepared by MediaPageController
// Note: This view uses JavaScript to load media dynamically
?>
<div class="page-header">
    <h1>Media Library</h1>
    <button class="btn btn-primary" onclick="showUploadModal()">Upload Media</button>
</div>

<div class="media-filters">
    <div class="filter-group">
        <input type="text" id="searchMedia" placeholder="Search files..." onkeyup="searchMedia()">
        <select id="filterType" onchange="filterMedia()">
            <option value="">All Types</option>
            <option value="image">Images</option>
            <option value="video">Videos</option>
            <option value="document">Documents</option>
        </select>
    </div>
</div>

<div class="media-grid" id="mediaGrid">
    <p>Loading...</p>
</div>

<div class="pagination" id="pagination"></div>

<!-- Upload Modal -->
<div id="uploadModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeUploadModal()">&times;</span>
        <h2>Upload Media</h2>
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label>Select File:</label>
                <input type="file" name="file" id="fileInput" required>
            </div>
            <div class="form-group">
                <label>Alt Text:</label>
                <input type="text" name="alt_text" id="altText">
            </div>
            <div class="form-group">
                <label>Description:</label>
                <textarea name="description" id="description" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closePreviewModal()">&times;</span>
        <div id="previewContent"></div>
    </div>
</div>

<script src="/admin/assets/js/media-library.js"></script>
<script>
let currentPage = 0;
const pageSize = 24;

function loadMedia(page = 0) {
    currentPage = page;
    const search = document.getElementById('searchMedia').value;
    const type = document.getElementById('filterType').value;
    const offset = page * pageSize;

    fetch(`/admin/api/media?limit=${pageSize}&offset=${offset}&type=${type}&search=${encodeURIComponent(search)}`)
        .then(r => r.json())
        .then(data => {
            displayMedia(data.files || []);
            displayPagination(data.total || 0, pageSize, page);
        });
}

function displayMedia(files) {
    const grid = document.getElementById('mediaGrid');
    if (files.length === 0) {
        grid.innerHTML = '<p>No media files found.</p>';
        return;
    }

    grid.innerHTML = files.map(file => `
        <div class="media-item" onclick="previewMedia(${file.id})">
            <img src="${file.thumbnail_url || file.url}" alt="${file.original_filename}" onerror="this.src='/admin/assets/images/placeholder.png'">
            <div class="media-info">
                <p class="media-name">${file.original_filename}</p>
                <p class="media-meta">${file.formatted_size || ''} • ${file.type}</p>
            </div>
            <div class="media-actions">
                <button class="btn btn-sm" onclick="event.stopPropagation(); selectMedia(${file.id})">Select</button>
                <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteMedia(${file.id})">Delete</button>
            </div>
        </div>
    `).join('');
}

function displayPagination(total, pageSize, currentPage) {
    const totalPages = Math.ceil(total / pageSize);
    const pagination = document.getElementById('pagination');
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }

    let html = '';
    for (let i = 0; i < totalPages; i++) {
        html += `<button class="btn btn-sm ${i === currentPage ? 'btn-primary' : ''}" onclick="loadMedia(${i})">${i + 1}</button>`;
    }
    pagination.innerHTML = html;
}

function searchMedia() {
    loadMedia(0);
}

function filterMedia() {
    loadMedia(0);
}

function showUploadModal() {
    document.getElementById('uploadModal').style.display = 'block';
}

function closeUploadModal() {
    document.getElementById('uploadModal').style.display = 'none';
    document.getElementById('uploadForm').reset();
}

function previewMedia(id) {
    fetch(`/admin/api/media?id=${id}`)
        .then(r => r.json())
        .then(data => {
            const content = document.getElementById('previewContent');
            content.innerHTML = `
                <h3>${data.original_filename}</h3>
                <img src="${data.url}" style="max-width: 100%;">
                <p><strong>Type:</strong> ${data.type}</p>
                <p><strong>Size:</strong> ${data.formatted_size}</p>
                ${data.width ? `<p><strong>Dimensions:</strong> ${data.width}x${data.height}</p>` : ''}
                <p><strong>URL:</strong> <input type="text" value="${data.url}" readonly style="width: 100%;"></p>
            `;
            document.getElementById('previewModal').style.display = 'block';
        });
}

function closePreviewModal() {
    document.getElementById('previewModal').style.display = 'none';
}

function deleteMedia(id) {
    if (!confirm('Are you sure you want to delete this media file?')) {
        return;
    }

    fetch('/admin/api/media', {
        method: 'DELETE',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: id})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            loadMedia(currentPage);
        } else {
            alert('Error: ' + (data.error || 'Failed to delete'));
        }
    });
}

function selectMedia(id) {
    fetch(`/admin/api/media?id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (window.opener && window.opener.onEditorMediaSelected) {
                window.opener.onEditorMediaSelected(data.id, data.url, data.thumbnail_url || data.url);
                window.close();
                return;
            }
            
            if (window.opener && window.opener.onMediaSelected) {
                window.opener.onMediaSelected(id);
                window.close();
                return;
            }
            
            navigator.clipboard.writeText(data.url);
            alert('URL copied to clipboard!');
        });
}

// Load media on page load
loadMedia();

// Upload form handler
const uploadForm = document.getElementById('uploadForm');
if (uploadForm) {
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = uploadForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Uploading...';
        
        fetch('/admin/api/media', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeUploadModal();
                loadMedia(0);
            } else {
                alert('Error: ' + (data.error || 'Upload failed'));
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });
}
</script>

<style>
.media-filters {
    margin-bottom: 20px;
    display: flex;
    gap: 15px;
}
.filter-group {
    display: flex;
    gap: 10px;
}
.filter-group input,
.filter-group select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
}
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}
.media-item {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.2s;
}
.media-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.media-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}
.media-info {
    padding: 10px;
}
.media-name {
    font-weight: 600;
    margin-bottom: 5px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.media-meta {
    font-size: 12px;
    color: #666;
}
.media-actions {
    padding: 10px;
    display: flex;
    gap: 5px;
    border-top: 1px solid #eee;
}
.pagination {
    display: flex;
    gap: 5px;
    justify-content: center;
    margin-top: 20px;
}
</style>

