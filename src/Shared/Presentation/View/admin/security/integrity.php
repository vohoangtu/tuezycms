<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">System Integrity Status</h4>
             <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="/admin/security">Security</a></li>
                    <li class="breadcrumb-item active">Integrity</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="card-title">Overall Status</h4>
                <div class="my-4" id="status-icon">
                    <?php if ($isSecure): ?>
                        <i class="mdi mdi-check-circle-outline text-success display-1"></i>
                        <h2 class="mt-3 text-success">Valid</h2>
                        <p class="text-muted">Core system files match the signed manifest.</p>
                    <?php else: ?>
                         <i class="mdi mdi-alert-circle-outline text-danger display-1"></i>
                         <h2 class="mt-3 text-danger">Compromised</h2>
                         <p class="text-muted">Files have been modified or signature is invalid.</p>
                    <?php endif; ?>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" id="btn-scan">
                        <i class="mdi mdi-magnify me-1"></i> Run Detailed Scan
                    </button>
                    <button class="btn btn-warning" id="btn-approve" style="display: none;">
                        <i class="mdi mdi-check-all me-1"></i> Approve Changes (Update Manifest)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card" id="scan-results-card" style="display: none;">
            <div class="card-body">
                <h4 class="card-title mb-4">Detailed Scan Results</h4>
                
                <div id="scan-loading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Scanning file system...</p>
                </div>

                <div id="scan-content">
                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#modified" role="tab">
                                <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                <span class="d-none d-sm-block">Modified <span class="badge bg-warning rounded-pill ms-1" id="count-modified">0</span></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#added" role="tab">
                                <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                <span class="d-none d-sm-block">Added <span class="badge bg-success rounded-pill ms-1" id="count-added">0</span></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#deleted" role="tab">
                                <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                <span class="d-none d-sm-block">Deleted <span class="badge bg-danger rounded-pill ms-1" id="count-deleted">0</span></span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        <div class="tab-pane active" id="modified" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <tbody id="list-modified"></tbody>
                                </table>
                            </div>
                            <div id="empty-modified" class="text-center py-3">No modified files.</div>
                        </div>
                        <div class="tab-pane" id="added" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <tbody id="list-added"></tbody>
                                </table>
                            </div>
                             <div id="empty-added" class="text-center py-3">No new files.</div>
                        </div>
                        <div class="tab-pane" id="deleted" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <tbody id="list-deleted"></tbody>
                                </table>
                            </div>
                             <div id="empty-deleted" class="text-center py-3">No deleted files.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btn-scan').addEventListener('click', async function() {
    const btn = this;
    const card = document.getElementById('scan-results-card');
    const loading = document.getElementById('scan-loading');
    const content = document.getElementById('scan-content');
    const btnApprove = document.getElementById('btn-approve');

    btn.disabled = true;
    card.style.display = 'block';
    loading.style.display = 'block';
    content.style.display = 'none';
    btnApprove.style.display = 'none';

    try {
        const response = await fetch('/api/security/integrity/scan', { method: 'POST' });
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            updateTab('modified', data.modified, 'text-warning');
            updateTab('added', data.added, 'text-success');
            updateTab('deleted', data.deleted, 'text-danger');

            if (data.status === 'compromised' || data.added.length > 0 || data.modified.length > 0 || data.deleted.length > 0) {
                btnApprove.style.display = 'block';
            }
        } else {
            alert('Scan failed: ' + result.error);
        }
    } catch (err) {
        console.error(err);
        alert('Network error during scan');
    } finally {
        btn.disabled = false;
        loading.style.display = 'none';
        content.style.display = 'block';
    }
});

document.getElementById('btn-approve').addEventListener('click', async function() {
    if (!confirm('Are you sure you want to approve these changes? This will update the system manifest and consider current files as "clean".')) {
        return;
    }

    try {
        const response = await fetch('/api/security/integrity/approve', { method: 'POST' });
        const result = await response.json();
        
        if (result.success) {
            alert('Manifest updated successfully. Reloading...');
            window.location.reload();
        } else {
            alert('Approval failed: ' + result.error);
        }
    } catch (err) {
        console.error(err);
        alert('Approving failed');
    }
});

function updateTab(type, files, colorClass) {
    const list = document.getElementById('list-' + type);
    const count = document.getElementById('count-' + type);
    const empty = document.getElementById('empty-' + type);

    list.innerHTML = '';
    count.textContent = files.length;

    if (files.length === 0) {
        empty.style.display = 'block';
    } else {
        empty.style.display = 'none';
        files.forEach(file => {
            const row = document.createElement('tr');
            row.innerHTML = `<td class="${colorClass}">${file}</td>`;
            list.appendChild(row);
        });
    }
}
</script>
