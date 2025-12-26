<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Security Logs</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="/admin/security">Security</a></li>
                    <li class="breadcrumb-item active">Logs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered dt-responsive nowrap w-100" id="logs-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Action</th>
                                <th>Level</th>
                                <th>IP Address</th>
                                <th>User ID</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= $log->getCreatedAt()->format('Y-m-d H:i:s') ?></td>
                                    <td><?= htmlspecialchars($log->getAction()) ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = match($log->getLevel()) {
                                            'critical' => 'bg-danger',
                                            'warning' => 'bg-warning',
                                            'info' => 'bg-info',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= $log->getLevel() ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($log->getIpAddress()) ?></td>
                                    <td><?= $log->getUserId() ?></td>
                                    <td><?= htmlspecialchars($log->getDescription()) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
