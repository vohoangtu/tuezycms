<?php
// View for products management page - Data prepared by ProductPageController
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Quản lý sản phẩm</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Admin</a></li>
                    <li class="breadcrumb-item active">Sản phẩm</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Danh sách sản phẩm</h4>
                <div class="flex-shrink-0">
                    <button type="button" class="btn btn-primary" onclick="showProductForm()">
                        <i class="ri-add-line align-middle me-1"></i> Thêm sản phẩm
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive table-card">
                    <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                        <thead class="text-muted table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Tên sản phẩm</th>
                                <th scope="col">SKU</th>
                                <th scope="col">Giá cũ</th>
                                <th scope="col">Giá mới</th>
                                <th scope="col">Giá KM</th>
                                <th scope="col">Tồn kho</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col" class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= $product->getId() ?></td>
                                <td><?= htmlspecialchars($product->getName()) ?></td>
                                <td><span class="badge bg-info-subtle text-info"><?= htmlspecialchars($product->getSku()) ?></span></td>
                                <td><?= number_format($product->getOldPrice(), 0, ',', '.') ?> đ</td>
                                <td class="fw-medium"><?= number_format($product->getNewPrice(), 0, ',', '.') ?> đ</td>
                                <td><?= $product->getPromotionalPrice() ? '<span class="text-danger fw-bold">' . number_format($product->getPromotionalPrice(), 0, ',', '.') . ' đ</span>' : '-' ?></td>
                                <td><span class="badge bg-<?= $product->getStock() > 0 ? 'success' : 'danger' ?>-subtle text-<?= $product->getStock() > 0 ? 'success' : 'danger' ?>"><?= $product->getStock() ?></span></td>
                                <td>
                                    <span class="badge bg-<?= $product->getStatus() === 'published' ? 'success' : ($product->getStatus() === 'draft' ? 'warning' : 'secondary') ?>-subtle text-<?= $product->getStatus() === 'published' ? 'success' : ($product->getStatus() === 'draft' ? 'warning' : 'secondary') ?>">
                                        <?= htmlspecialchars($product->getStatus()) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="button" class="btn btn-sm btn-soft-primary" onclick="editProduct(<?= $product->getId() ?>)">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteProduct(<?= $product->getId() ?>)">
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
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Thêm/Sửa sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeProductModal()"></button>
            </div>
            <form id="productForm" method="POST" action="/admin/api/products" onsubmit="return submitProductForm(event)" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="productId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productName" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="productName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productSlug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="slug" id="productSlug" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productCategoryId" class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" id="productCategoryId" required>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->getId() ?>"><?= htmlspecialchars($category->getName()) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productSku" class="form-label">SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="sku" id="productSku" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productShortDescription" class="form-label">Mô tả ngắn</label>
                        <textarea class="form-control" name="short_description" id="productShortDescription" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Mô tả chi tiết <span class="text-danger">*</span></label>
                        <textarea name="description" id="productDescription" class="form-control rich-editor" rows="10" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="productOldPrice" class="form-label">Giá cũ <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="old_price" id="productOldPrice" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="productNewPrice" class="form-label">Giá mới <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="new_price" id="productNewPrice" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="productPromotionalPrice" class="form-label">Giá khuyến mãi</label>
                            <input type="number" class="form-control" name="promotional_price" id="productPromotionalPrice" step="0.01">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productStock" class="form-label">Tồn kho <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="stock" id="productStock" value="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productStatus" class="form-label">Trạng thái</label>
                            <select class="form-select" name="status" id="productStatus">
                                <option value="draft">Nháp</option>
                                <option value="published">Đã xuất bản</option>
                                <option value="out_of_stock">Hết hàng</option>
                            </select>
                        </div>
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
                        <input type="hidden" name="featured_image" id="productFeaturedImage">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Gallery Images</label>
                        <div id="galleryPreview" class="d-flex flex-wrap gap-2 mb-2"></div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="selectGalleryImage()">Add Image</button>
                        <input type="hidden" name="images" id="productImages">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" onclick="closeProductModal()">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showProductForm() {
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('featuredImagePreview').innerHTML = '<p class="text-muted mb-0">No image selected</p>';
    document.getElementById('productFeaturedImage').value = '';
    galleryImages = [];
    updateGalleryPreview();
    modal.show();
    setTimeout(() => initProductEditor(), 100);
}

function initProductEditor() {
    if (typeof tinymce === 'undefined') {
        console.warn('TinyMCE not loaded');
        return;
    }
    
    const editorId = 'productDescription';
    const editor = tinymce.get(editorId);
    
    if (editor) {
        tinymce.remove('#' + editorId);
    }
    
    setTimeout(() => {
        tinymce.init({
            selector: '#' + editorId,
            height: 500,
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image | code | help',
        });
    }, 100);
}

let galleryImages = [];

function editProduct(id) {
    fetch(`/admin/api/products?id=${id}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('productId').value = data.id;
            document.getElementById('productName').value = data.name;
            document.getElementById('productSlug').value = data.slug;
            document.getElementById('productCategoryId').value = data.category_id;
            document.getElementById('productShortDescription').value = data.short_description || '';
            document.getElementById('productOldPrice').value = data.old_price;
            document.getElementById('productNewPrice').value = data.new_price;
            document.getElementById('productPromotionalPrice').value = data.promotional_price || '';
            document.getElementById('productSku').value = data.sku;
            document.getElementById('productStock').value = data.stock;
            document.getElementById('productStatus').value = data.status;
            
            if (data.featured_image) {
                loadFeaturedImagePreview(data.featured_image);
            }
            
            if (data.images && Array.isArray(data.images)) {
                galleryImages = data.images;
                updateGalleryPreview();
            } else {
                galleryImages = [];
                updateGalleryPreview();
            }
            
            const modal = new bootstrap.Modal(document.getElementById('productModal'));
            modal.show();
            initProductEditor();
            
            setTimeout(() => {
                const editor = tinymce.get('productDescription');
                if (editor) {
                    editor.setContent(data.description || '');
                } else {
                    document.getElementById('productDescription').value = data.description || '';
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
            document.getElementById('productFeaturedImage').value = mediaId;
            document.querySelector('[onclick="removeFeaturedImage()"]').style.display = 'inline-block';
        });
}

function removeFeaturedImage() {
    document.getElementById('featuredImagePreview').innerHTML = '<p class="text-muted mb-0">No image selected</p>';
    document.getElementById('productFeaturedImage').value = '';
    document.querySelector('[onclick="removeFeaturedImage()"]').style.display = 'none';
}

function selectGalleryImage() {
    const width = 800;
    const height = 600;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;
    
    window.onMediaSelected = function(mediaId) {
        if (!galleryImages.includes(mediaId)) {
            galleryImages.push(mediaId);
            updateGalleryPreview();
        }
    };
    
    window.open('/admin/media', 'mediaPicker', `width=${width},height=${height},left=${left},top=${top}`);
}

function removeGalleryImage(mediaId) {
    galleryImages = galleryImages.filter(id => id != mediaId);
    updateGalleryPreview();
}

function updateGalleryPreview() {
    const preview = document.getElementById('galleryPreview');
    document.getElementById('productImages').value = JSON.stringify(galleryImages);
    
    if (galleryImages.length === 0) {
        preview.innerHTML = '<p class="text-muted">No images selected</p>';
        return;
    }
    
    Promise.all(galleryImages.map(id => 
        fetch(`/admin/api/media?id=${id}`).then(r => r.json())
    )).then(files => {
        preview.innerHTML = files.map((file, index) => `
            <div class="border rounded p-2" style="position: relative;">
                <img src="${file.thumbnail_url}" alt="${file.original_filename}" style="max-width: 100px; max-height: 100px; object-fit: contain;">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" onclick="removeGalleryImage(${file.id})" style="transform: translate(50%, -50%);">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        `).join('');
    });
}

function deleteProduct(id) {
    if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
        fetch(`/admin/api/products`, {
            method: 'DELETE',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id})
        }).then(() => location.reload());
    }
}

function closeProductModal() {
    if (typeof tinymce !== 'undefined') {
        tinymce.remove('#productDescription');
    }
    const modal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
    if (modal) {
        modal.hide();
    }
}

function submitProductForm(event) {
    event.preventDefault();
    
    const editor = tinymce.get('productDescription');
    if (editor) {
        const content = editor.getContent();
        document.getElementById('productDescription').value = content;
    }
    
    const form = document.getElementById('productForm');
    const formData = new FormData(form);
    
    fetch('/admin/api/products', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success || data.id) {
            closeProductModal();
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
