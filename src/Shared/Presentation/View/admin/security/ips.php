<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Blocked IP Management</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="/admin/security">Security</a></li>
                    <li class="breadcrumb-item active">Blocked IPs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Block New IP</h4>
                <form id="block-ip-form">
                    <div class="mb-3">
                        <label class="form-label">IP Address</label>
                        <input type="text" class="form-control" name="ip_address" required placeholder="x.x.x.x">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <input type="text" class="form-control" name="reason" required placeholder="e.g. Malicious activity">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration (Minutes)</label>
                        <input type="number" class="form-control" name="duration" placeholder="Leave empty for permanent">
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-danger">Block IP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Blocked IPs List</h4>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Reason</th>
                                <th>Expires At</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ips as $ip): ?>
                                <tr>
                                    <td><?= htmlspecialchars($ip->getIpAddress()) ?></td>
                                    <td><?= htmlspecialchars($ip->getReason()) ?></td>
                                    <td>
                                        <?= $ip->getExpiresAt() ? $ip->getExpiresAt()->format('Y-m-d H:i') : 'Permanent' ?>
                                    </td>
                                    <td>
                                        <?php if ($ip->isActive()): ?>
                                            <span class="badge bg-danger">Blocked</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($ip->isActive()): ?>
                                            <button class="btn btn-sm btn-outline-success unblock-btn" data-ip="<?= $ip->getIpAddress() ?>">
                                                Unblock
                                            </button>
                                        <?php endif; ?>
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

<script>
document.getElementById('block-ip-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    try {
        const response = await fetch('/admin/api/security/ips', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            window.location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (err) {
        console.error(err);
        alert('Failed to block IP');
    }
});

document.querySelectorAll('.unblock-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const ip = this.dataset.ip;
        if (!confirm('Unblock ' + ip + '?')) return;
        
        try {
            const response = await fetch('/admin/api/security/ips/' + ip, {
                method: 'DELETE'
            });
            const result = await response.json();
            if (result.success) {
                window.location.reload();
            } else {
                alert('Error: ' + result.error);
            }
        } catch (err) {
            console.error(err);
            alert('Failed to unblock IP');
        }
    });
});
</script>
