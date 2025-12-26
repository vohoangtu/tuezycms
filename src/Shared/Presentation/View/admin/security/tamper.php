<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Source Code Tamper Protection</h4>
             <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="/admin/security">Security</a></li>
                    <li class="breadcrumb-item active">Anti-Tamper</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Private Key Management</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Developer Only:</strong> Use this to generate the Private Key needed for signing code.
                    The Private Key is stored in <code>storage/security/keys/private.pem</code>.
                </div>

                <div class="text-center my-4">
                    <?php if ($hasKey): ?>
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-soft-success text-success rounded-circle display-5">
                                <i class="ri-key-2-fill"></i>
                            </div>
                        </div>
                        <h4 class="text-success">Keys Exist</h4>
                        <p class="text-muted">Private Key is present on this server.</p>
                    <?php else: ?>
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-soft-warning text-warning rounded-circle display-5">
                                <i class="ri-key-2-line"></i>
                            </div>
                        </div>
                        <h4 class="text-warning">No Private Key</h4>
                        <p class="text-muted">You cannot sign code without generating keys first.</p>
                    <?php endif; ?>
                </div>

                <div class="d-grid gap-2">
                    <button id="btn-keygen" class="btn btn-primary" onclick="generateKeys()">
                        <i class="ri-refresh-line align-middle me-1"></i> Generate New Keypair
                    </button>
                    <!-- Download button could be added here -->
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">System Signing</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <strong>Warning:</strong> Signing the system will update the <code>integrity.sig</code>.
                    Any subsequent modification to source files will cause the system to <strong>STOP WORKING</strong> (Error 503) until resigned.
                </div>

                <div class="text-center my-4">
                    <?php if ($isSigned): ?>
                         <div class="mb-3">
                            <i class="ri-shield-check-fill text-success display-4"></i>
                        </div>
                        <h4 class="text-success">System Signed</h4>
                        <p class="text-muted">Integrity protection is ACTIVE.</p>
                    <?php else: ?>
                        <div class="mb-3">
                            <i class="ri-shield-line text-secondary display-4"></i>
                        </div>
                        <h4 class="text-secondary">Unsigned</h4>
                        <p class="text-muted">System is running in permissive mode.</p>
                    <?php endif; ?>
                </div>

                <div class="d-grid gap-2">
                    <button id="btn-sign" class="btn btn-danger" onclick="signSystem()" <?php echo (!$hasKey) ? 'disabled' : ''; ?>>
                        <i class="ri-quill-pen-line align-middle me-1"></i> Sign Source Code
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function generateKeys() {
    if (!confirm('WARNING: This will overwrite any existing keys! If you lose the old private key, you cannot sign updates for old deployments. Continue?')) {
        return;
    }

    const btn = document.getElementById('btn-keygen');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line ri-spin align-middle me-1"></i> Generating...';

    try {
        const response = await fetch('/api/security/tamper/keygen', { method: 'POST' });
        const result = await response.json();
        
        if (result.success) {
            alert('Keys generated successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + result.error);
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (e) {
        alert('Network Error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function signSystem() {
    if (!confirm('CAUTION: This will LOCK the system to the current state. Any modification after this will cause downtime until re-signed. Proceed?')) {
        return;
    }

    const btn = document.getElementById('btn-sign');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line ri-spin align-middle me-1"></i> Signing...';

    try {
        const response = await fetch('/api/security/tamper/sign', { method: 'POST' });
        const result = await response.json();
        
        if (result.success) {
            alert('System Signed Successfully! Anti-Tamper is now Active.');
            window.location.reload();
        } else {
            alert('Error: ' + result.error);
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (e) {
        alert('Network Error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}
</script>
