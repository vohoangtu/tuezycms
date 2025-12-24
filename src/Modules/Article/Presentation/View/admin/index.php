<?php
// View for articles management page
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Quản lý bài viết</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Admin</a></li>
                    <li class="breadcrumb-item active">Bài viết</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Danh sách bài viết</h4>
                <div class="flex-shrink-0">
                    <button type="button" class="btn btn-primary" onclick="showArticleForm()">
                        <i class="ri-add-line align-middle me-1"></i> Thêm bài viết
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive table-card">
                    <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                        <thead class="text-muted table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Tiêu đề</th>
                                <th scope="col">Loại</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col">Lượt xem</th>
                                <th scope="col">Ngày tạo</th>
                                <th scope="col" class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articles as $article): ?>
                            <tr>
                                <td><?= $article->getId() ?></td>
                                <td><?= htmlspecialchars($article->getTitle()) ?></td>
                                <td><span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($article->getType()->getName()) ?></span></td>
                                <td>
                                    <span class="badge bg-<?= $article->getStatus() === 'published' ? 'success' : ($article->getStatus() === 'draft' ? 'warning' : 'secondary') ?>-subtle text-<?= $article->getStatus() === 'published' ? 'success' : ($article->getStatus() === 'draft' ? 'warning' : 'secondary') ?>">
                                        <?= htmlspecialchars($article->getStatus()) ?>
                                    </span>
                                </td>
                                <td><?= $article->getViews() ?></td>
                                <td><?= $article->getCreatedAt()->format('d/m/Y H:i') ?></td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="button" class="btn btn-sm btn-soft-primary" onclick="editArticle(<?= $article->getId() ?>)">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteArticle(<?= $article->getId() ?>)">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="articleModal" tabindex="-1" aria-labelledby="articleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="articleModalLabel">Thêm/Sửa bài viết</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeModal()"></button>
            </div>
            <form id="articleForm" method="POST" action="/admin/api/articles" onsubmit="return submitArticleForm(event)" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="articleId">
                    
                    <div class="mb-3">
                        <label for="articleTitle" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="articleTitle" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="articleSlug" class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="slug" id="articleSlug" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="articleTypeId" class="form-label">Loại bài viết <span class="text-danger">*</span></label>
                        <select class="form-select" name="type_id" id="articleTypeId" required>
                            <?php foreach ($types as $type): ?>
                            <option value="<?= $type->getId() ?>"><?= htmlspecialchars($type->getName()) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="articleContent" class="form-label">Nội dung <span class="text-danger">*</span></label>
                        <textarea name="content" id="articleContent" class="form-control rich-editor" rows="10" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="articleStatus" class="form-label">Trạng thái</label>
                        <select class="form-select" name="status" id="articleStatus">
                            <option value="draft">Nháp</option>
                            <option value="published">Đã xuất bản</option>
                            <option value="archived">Lưu trữ</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="articleMetaTitle" class="form-label">Meta Title</label>
                        <input type="text" class="form-control" name="meta_title" id="articleMetaTitle">
                    </div>
                    
                    <div class="mb-3">
                        <label for="articleMetaDescription" class="form-label">Meta Description</label>
                        <textarea class="form-control" name="meta_description" id="articleMetaDescription" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Featured Image</label>
                        <div class="d-flex align-items-center gap-2">
                            <div id="featuredImagePreview" class="border rounded p-2" style="min-height: 100px; display: flex; align-items: center; justify-content: center;">
                                <p class="text-muted mb-0">No image selected</p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-primary" onclick="selectFeaturedImage()">Select Image</button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeFeaturedImage()" style="display: none;">Remove</button>
                            </div>
                        </div>
                        <input type="hidden" name="featured_image" id="articleFeaturedImage">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" onclick="closeModal()">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showArticleForm() {
    const modal = new bootstrap.Modal(document.getElementById('articleModal'));
    document.getElementById('articleForm').reset();
    document.getElementById('articleId').value = '';
    document.getElementById('featuredImagePreview').innerHTML = '<p class="text-muted mb-0">No image selected</p>';
    document.getElementById('articleFeaturedImage').value = '';
    document.querySelector('[onclick="removeFeaturedImage()"]').style.display = 'none';
    modal.show();
}

function editArticle(id) {
    fetch(`/admin/api/articles?id=${id}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('articleId').value = data.id;
            document.getElementById('articleTitle').value = data.title;
            document.getElementById('articleSlug').value = data.slug;
            document.getElementById('articleTypeId').value = data.type_id;
            document.getElementById('articleStatus').value = data.status;
            document.getElementById('articleMetaTitle').value = data.meta_title || '';
            document.getElementById('articleMetaDescription').value = data.meta_description || '';
            
            if (data.featured_image) {
                loadFeaturedImagePreview(data.featured_image);
            } else {
                document.getElementById('featuredImagePreview').innerHTML = '<p class="text-muted mb-0">No image selected</p>';
                document.getElementById('articleFeaturedImage').value = '';
                document.querySelector('[onclick="removeFeaturedImage()"]').style.display = 'none';
            }
            
            const modal = new bootstrap.Modal(document.getElementById('articleModal'));
            modal.show();
            
            setTimeout(() => {
                const editor = tinymce.get('articleContent');
                if (editor) {
                    editor.setContent(data.content || '');
                } else {
                    document.getElementById('articleContent').value = data.content || '';
                }
            }, 300);
        });
}

function selectFeaturedImage() {
    const width = 800;
    const height = 600;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;
    
    window.onMediaSelected = function(mediaId) {
        loadFeaturedImagePreview(mediaId);
    };
    
    window.open('/admin/media', 'mediaPicker', `width=${width},height=${height},left=${left},top=${top}`);
}

function loadFeaturedImagePreview(mediaId) {
    fetch(`/admin/api/media?id=${mediaId}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('featuredImagePreview').innerHTML = `<img src="${data.thumbnail_url}" alt="${data.original_filename}" style="max-width: 150px; max-height: 100px; object-fit: contain;">`;
            document.getElementById('articleFeaturedImage').value = mediaId;
            document.querySelector('[onclick="removeFeaturedImage()"]').style.display = 'inline-block';
        });
}

function removeFeaturedImage() {
    document.getElementById('featuredImagePreview').innerHTML = '<p class="text-muted mb-0">No image selected</p>';
    document.getElementById('articleFeaturedImage').value = '';
    document.querySelector('[onclick="removeFeaturedImage()"]').style.display = 'none';
}

function deleteArticle(id) {
    if (confirm('Bạn có chắc muốn xóa bài viết này?')) {
        fetch(`/admin/api/articles`, {
            method: 'DELETE',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id})
        }).then(() => location.reload());
    }
}

function closeModal() {
    if (typeof tinymce !== 'undefined') {
        tinymce.remove('#articleContent');
    }
    const modal = bootstrap.Modal.getInstance(document.getElementById('articleModal'));
    if (modal) {
        modal.hide();
    }
}

function submitArticleForm(event) {
    event.preventDefault();
    
    const editor = tinymce.get('articleContent');
    if (editor) {
        const content = editor.getContent();
        document.getElementById('articleContent').value = content;
    }
    
    const form = document.getElementById('articleForm');
    const formData = new FormData(form);
    
    fetch('/admin/api/articles', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success || data.id) {
            closeModal();
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to save'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
    
    return false;
}
</script>
