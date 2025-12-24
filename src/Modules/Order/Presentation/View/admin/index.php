<?php
// View for orders management page - Data prepared by OrderPageController
?>
<div class="page-header">
    <h1>Quản lý đơn hàng</h1>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Thanh toán</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">
                    Chưa có đơn hàng nào
                </td>
            </tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order->getOrderNumber()) ?></td>
                    <td>Customer #<?= $order->getCustomerId() ?></td>
                    <td><?= number_format($order->getTotal(), 0, ',', '.') ?> đ</td>
                    <td><?= htmlspecialchars($order->getStatus()) ?></td>
                    <td><?= htmlspecialchars($order->getPaymentStatus()) ?></td>
                    <td><?= $order->getCreatedAt()->format('d/m/Y H:i') ?></td>
                    <td>
                        <button class="btn btn-sm" onclick="viewOrder(<?= $order->getId() ?>)">Xem</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function viewOrder(id) {
    window.location.href = `/admin/orders/${id}`;
}
</script>

