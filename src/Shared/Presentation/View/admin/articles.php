<?php
/**
 * Articles Management View
 */

$articles = $articles ?? [];
$types = $types ?? [];
?>

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Quản lý bài viết</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Bài viết</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Danh sách bài viết</h4>
                <div class="flex-shrink-0">
                    <button type="button" class="btn btn-success" onclick="createArticle()">
                        <i class="ri-add-line align-bottom me-1"></i> Tạo bài viết mới
                    </button>
                </div>
            </div><!-- end card header -->

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-nowrap align-middle">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Tiêu đề</th>
                                <th scope="col">Loại</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($articles)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Chưa có bài viết nào</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($articles as $article): ?>
                                    <tr>
                                        <td><?= $article['id'] ?? '' ?></td>
                                        <td><?= htmlspecialchars($article['title'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($article['type_name'] ?? '') ?></td>
                                        <td>
                                            <span class="badge bg-<?= ($article['status'] ?? '') === 'published' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($article['status'] ?? 'draft') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="editArticle(<?= $article['id'] ?? 0 ?>)">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteArticle(<?= $article['id'] ?? 0 ?>)">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div><!-- end card-body -->
        </div><!-- end card -->
    </div>
    <!-- end col -->
</div>
<!-- end row -->

<script>
function createArticle() {
    // TODO: Implement create article modal/page
    alert('Chức năng tạo bài viết sẽ được implement sau');
}

function editArticle(id) {
    // TODO: Implement edit article
    alert('Chức năng sửa bài viết #' + id + ' sẽ được implement sau');
}

function deleteArticle(id) {
    if (confirm('Bạn có chắc muốn xóa bài viết này?')) {
        // TODO: Implement delete article
        alert('Chức năng xóa bài viết sẽ được implement sau');
    }
}
</script>
