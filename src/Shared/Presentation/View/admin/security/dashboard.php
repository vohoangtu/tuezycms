<!-- admin/security/dashboard.php -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Security Dashboard</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active">Security</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row">
        <div class="col-xl-4 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">System Integrity</p>
                            <h4 class="mb-2">
                                <?php if ($isSecure): ?>
                                    <span class="text-success"><i class="mdi mdi-shield-check"></i> Secure</span>
                                <?php else: ?>
                                    <span class="text-danger"><i class="mdi mdi-shield-alert"></i> Compromised</span>
                                <?php endif; ?>
                            </h4>
                            <p class="text-muted mb-0"><span class="text-success fw-bold font-size-12 me-2"><i class="mdi mdi-clock-outline align-middle me-1"></i></span> Last Checked: Now</p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="mdi mdi-security font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
             <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Anti-Tamper</p>
                            <h4 class="mb-2">Protection Tools</h4>
                            <div class="mt-3">
                                <a href="/admin/security/tamper" class="btn btn-soft-warning btn-sm">Manage Keys & Sign</a>
                            </div>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
                                <i class="mdi mdi-key-variant font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Recent Activity Logs</h4>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Action</th>
                                    <th>IP Address</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>
                                        <h5 class="font-size-14 mb-1"><?= htmlspecialchars($log->getAction()) ?></h5>
                                        <p class="text-muted mb-0 font-size-12"><?= htmlspecialchars(substr($log->getDescription(), 0, 50)) ?></p>
                                    </td>
                                    <td><?= htmlspecialchars($log->getIpAddress()) ?></td>
                                    <td><?= $log->getCreatedAt()->format('H:i d/m') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="/admin/security/logs" class="btn btn-primary">View All Logs</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Blocked IPs</h4>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>IP Address</th>
                                    <th>Reason</th>
                                    <th>Expires</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blockedIps as $ip): ?>
                                <tr>
                                    <td><?= htmlspecialchars($ip->getIpAddress()) ?></td>
                                    <td><?= htmlspecialchars($ip->getReason()) ?></td>
                                    <td><?= $ip->getExpiresAt() ? $ip->getExpiresAt()->format('Y-m-d H:i') : 'Permanent' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="/admin/security/ips" class="btn btn-primary">Manage IPs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
