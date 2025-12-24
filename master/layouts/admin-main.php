<?php
/**
 * Admin Main Layout Wrapper
 * Integrates TuzyCMS page content with Velzon master theme
 */

// Get page content from MasterLayoutRenderer
$pageContent = $GLOBALS['pageContent'] ?? '';
$pageData = $GLOBALS['pageData'] ?? [];
$pageTitle = $pageData['pageTitle'] ?? 'Dashboard';

// Make includeFileWithVariables available globally
if (!function_exists('includeFileWithVariables')) {
    function includeFileWithVariables($filePath, $variables = array(), $print = true)
    {
        $output = NULL;
        if(file_exists($filePath)){
            // Extract the variables to a local namespace
            extract($variables);

            // Start output buffering
            ob_start();

            // Include the template file
            include $filePath;

            // End buffering and return its contents
            $output = ob_get_clean();
        }
        if ($print) {
            print $output;
        }
        return $output;
    }
}
?>
<!doctype html>
<html lang="vi" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">

<head>
    <?php includeFileWithVariables(__DIR__ . '/title-meta.php', array('title' => $pageTitle)); ?>
    <?php include __DIR__ . '/head-css.php'; ?>
</head>

<body>
    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include __DIR__ . '/menu.php'; ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php 
                    // Render the page content from TuzyCMS
                    echo $pageContent;
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

    <?php include __DIR__ . '/customizer.php'; ?>

    <?php include __DIR__ . '/vendor-scripts.php'; ?>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>

</body>

</html>
