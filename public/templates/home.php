<!DOCTYPE html>
<html lang="<?= $router->getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($translator->trans('home')) ?> - TuzyCMS</title>
    
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
    <header class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= $router->url('') ?>">TuzyCMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= $router->url('') ?>"><?= $translator->trans('home') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $router->url('san-pham') ?>"><?= $translator->trans('products') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $router->url('tin-tuc') ?>"><?= $translator->trans('news') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $router->url('lien-he') ?>"><?= $translator->trans('contact') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $router->url('cart') ?>"><?= $translator->trans('cart') ?></a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $router->getLocale() === 'vi' ? $router->url('', 'en') : $router->url('', 'vi') ?>">
                            <?= $router->getLocale() === 'vi' ? 'EN' : 'VI' ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="py-5 bg-primary bg-gradient">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h1 class="text-white mb-3">Chào mừng đến với TuzyCMS</h1>
                        <p class="text-white-50 lead">Hệ thống CMS hiện đại với đầy đủ tính năng</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section class="py-5">
            <div class="container">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-0"><?= htmlspecialchars($translator->trans('products')) ?></h2>
                    </div>
                </div>
                <div class="row g-4">
                    <?php foreach ($products as $product): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100">
                            <?php if ($product->getFeaturedImage()): ?>
                            <img src="<?= htmlspecialchars($product->getFeaturedImage()) ?>" class="card-img-top" alt="<?= htmlspecialchars($product->getName()) ?>" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($product->getName()) ?></h5>
                                <div class="mt-auto">
                                    <p class="card-text">
                                        <?php if ($product->getPromotionalPrice()): ?>
                                            <span class="text-decoration-line-through text-muted me-2"><?= number_format($product->getOldPrice(), 0, ',', '.') ?> đ</span>
                                            <span class="fw-bold text-danger"><?= number_format($product->getPromotionalPrice(), 0, ',', '.') ?> đ</span>
                                        <?php else: ?>
                                            <span class="fw-bold"><?= number_format($product->getNewPrice(), 0, ',', '.') ?> đ</span>
                                        <?php endif; ?>
                                    </p>
                                    <a href="<?= $router->url($product->getSlug()) ?>" class="btn btn-primary"><?= $translator->trans('read_more') ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Articles Section -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-0"><?= htmlspecialchars($translator->trans('news')) ?></h2>
                    </div>
                </div>
                <div class="row g-4">
                    <?php foreach ($articles as $article): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <?php if ($article->getFeaturedImage()): ?>
                            <img src="<?= htmlspecialchars($article->getFeaturedImage()) ?>" class="card-img-top" alt="<?= htmlspecialchars($article->getTitle()) ?>" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($article->getTitle()) ?></h5>
                                <p class="card-text text-muted small"><?= htmlspecialchars($article->getType()->getName()) ?> • <?= $article->getCreatedAt()->format('d/m/Y') ?></p>
                                <div class="mt-auto">
                                    <a href="<?= $router->url($article->getSlug()) ?>" class="btn btn-outline-primary"><?= $translator->trans('read_more') ?> →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; <?= date('Y') ?> TuzyCMS. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JAVASCRIPT -->
    <script src="/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/plugins.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>


