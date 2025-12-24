<!DOCTYPE html>
<html lang="<?= $router->getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($translator->trans('products')) ?> - TuzyCMS</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="container">
                <div class="logo">TuzyCMS</div>
                <ul class="menu">
                    <li><a href="<?= $router->url('') ?>"><?= $translator->trans('home') ?></a></li>
                    <li><a href="<?= $router->url('san-pham') ?>"><?= $translator->trans('products') ?></a></li>
                    <li><a href="<?= $router->url('tin-tuc') ?>"><?= $translator->trans('news') ?></a></li>
                    <li><a href="<?= $router->url('lien-he') ?>"><?= $translator->trans('contact') ?></a></li>
                    <li><a href="<?= $router->url('cart') ?>"><?= $translator->trans('cart') ?></a></li>
                    <li>
                        <a href="<?= $router->getLocale() === 'vi' ? $router->url('', 'en') : $router->url('', 'vi') ?>">
                            <?= $router->getLocale() === 'vi' ? 'EN' : 'VI' ?>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container" style="max-width: 1200px; margin: 40px auto; padding: 20px;">
            <h1><?= htmlspecialchars($translator->trans('products')) ?></h1>
            
            <div class="products-grid" style="margin-top: 30px;">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <?php if ($product->getFeaturedImage()): ?>
                    <img src="<?= htmlspecialchars($product->getFeaturedImage()) ?>" alt="<?= htmlspecialchars($product->getName()) ?>">
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($product->getName()) ?></h3>
                    <p class="price">
                        <?php if ($product->getPromotionalPrice()): ?>
                            <span class="old-price"><?= number_format($product->getOldPrice(), 0, ',', '.') ?> đ</span>
                            <span class="new-price"><?= number_format($product->getPromotionalPrice(), 0, ',', '.') ?> đ</span>
                        <?php else: ?>
                            <span class="new-price"><?= number_format($product->getNewPrice(), 0, ',', '.') ?> đ</span>
                        <?php endif; ?>
                    </p>
                    <a href="<?= $router->url($product->getSlug()) ?>" class="btn"><?= $translator->trans('read_more') ?></a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 TuzyCMS. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

