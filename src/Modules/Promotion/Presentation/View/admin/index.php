<?php
// View for promotions management page - Data prepared by PromotionPageController
?>
<div class="page-header">
    <h1>Quản lý khuyến mãi</h1>
    <button class="btn btn-primary" onclick="showPromotionForm()">Thêm khuyến mãi</button>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Mã</th>
                <th>Loại</th>
                <th>Giá trị</th>
                <th>Ngày bắt đầu</th>
                <th>Ngày kết thúc</th>
                <th>Đã dùng</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($promotions as $promotion): ?>
            <tr>
                <td><?= $promotion->getId() ?></td>
                <td><?= htmlspecialchars($promotion->getName()) ?></td>
                <td><?= htmlspecialchars($promotion->getCode()) ?></td>
                <td><?= htmlspecialchars($promotion->getType()->getLabel()) ?></td>
                <td><?= $promotion->getType()->value === 'percentage' ? $promotion->getValue() . '%' : number_format($promotion->getValue(), 0, ',', '.') . ' đ' ?></td>
                <td><?= $promotion->getStartDate()->format('d/m/Y H:i') ?></td>
                <td><?= $promotion->getEndDate()->format('d/m/Y H:i') ?></td>
                <td><?= $promotion->getUsedCount() ?>/<?= $promotion->getUsageLimit() ?: '∞' ?></td>
                <td><?= $promotion->isActive() ? 'Hoạt động' : 'Tắt' ?></td>
                <td>
                    <button class="btn btn-sm" onclick="editPromotion(<?= $promotion->getId() ?>)">Sửa</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function showPromotionForm() {
    alert('Form thêm khuyến mãi - sẽ được implement');
}

function editPromotion(id) {
    alert('Form sửa khuyến mãi - sẽ được implement');
}
</script>

