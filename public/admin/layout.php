<?php
/**
 * Admin Layout using Velzon Master Theme
 * Integrates with TuzyCMS MVC structure
 */

use Modules\User\Application\Service\AuthService;
use Core\Container\ServiceContainer;

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get AuthService and current user
$container = ServiceContainer::getInstance();
$authService = $container->make(AuthService::class);
$user = $authService->getCurrentUser();

// Get current page title
$currentPage = basename($pageFile ?? 'dashboard', '.php');
$pageTitle = ucfirst(str_replace(['-', '_'], ' ', $currentPage));

// Determine page title for meta
$title = $pageTitle . ' | TuzyCMS Admin';
?>
<!doctype html>
<html lang="vi" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">

<head>
    <meta charset="utf-8" />
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="TuzyCMS Admin Panel" name="description" />
    <meta content="TuzyCMS" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="/assets/images/favicon.ico" onerror="this.href='/assets/images/favicon.ico';">

    <!-- Layout config Js -->
    <script src="/assets/js/layout.js"></script>
    <!-- Bootstrap Css -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="/assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="/assets/css/custom.min.css" rel="stylesheet" type="text/css" />
</head>

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include __DIR__ . '/topbar.php'; ?>
        <?php include __DIR__ . '/menu.php'; ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php 
                    // Include the page content
                    if (isset($pageFile) && file_exists($pageFile)) {
                        include $pageFile; 
                    }
                    ?>

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?php include __DIR__ . '/footer.php'; ?>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <?php include __DIR__ . '/../../master/layouts/customizer.php'; ?>

    <!-- JAVASCRIPT -->
    <script src="/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/libs/simplebar/simplebar.min.js"></script>
    <script src="/assets/libs/node-waves/waves.min.js"></script>
    <script src="/assets/libs/feather-icons/feather.min.js"></script>
    <script src="/assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
    <script src="/assets/js/plugins.js"></script>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

    <!-- Additional scripts for admin pages -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        // TinyMCE configuration
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: 'textarea.rich-editor',
                height: 400,
                menubar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | ' +
                    'bold italic forecolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
            });
        }
    </script>

</body>

</html>
