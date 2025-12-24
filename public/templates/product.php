<!DOCTYPE html>
<html lang="<?= $router->getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product->getMetaTitle() ?: $product->getName()) ?> - TuzyCMS</title>
    <?php if ($product->getMetaDescription()): ?>
    <meta name="description" content="<?= htmlspecialchars($product->getMetaDescription()) ?>">
    <?php endif; ?>
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
                        <a href="<?= $router->getLocale() === 'vi' ? $router->url($product->getSlug(), 'en') : $router->url($product->getSlug(), 'vi') ?>">
                            <?= $router->getLocale() === 'vi' ? 'EN' : 'VI' ?>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container" style="max-width: 1200px; margin: 40px auto; padding: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                <div>
                    <?php if ($product->getFeaturedImage()): ?>
                    <img src="<?= htmlspecialchars($product->getFeaturedImage()) ?>" alt="<?= htmlspecialchars($product->getName()) ?>" style="width: 100%; border-radius: 8px;">
                    <?php endif; ?>
                </div>
                <div>
                    <h1><?= htmlspecialchars($product->getName()) ?></h1>
                    <p class="price" style="font-size: 1.5rem; margin: 20px 0;">
                        <?php if ($product->getPromotionalPrice()): ?>
                            <span class="old-price"><?= number_format($product->getOldPrice(), 0, ',', '.') ?> đ</span>
                            <span class="new-price"><?= number_format($product->getPromotionalPrice(), 0, ',', '.') ?> đ</span>
                        <?php else: ?>
                            <span class="new-price"><?= number_format($product->getNewPrice(), 0, ',', '.') ?> đ</span>
                        <?php endif; ?>
                    </p>
                    <p><strong>SKU:</strong> <?= htmlspecialchars($product->getSku()) ?></p>
                    <p><strong>Tồn kho:</strong> <?= $product->getStock() ?></p>
                    <p style="margin: 20px 0;"><?= nl2br(htmlspecialchars($product->getShortDescription())) ?></p>
                    <button class="btn" onclick="addToCart(<?= $product->getId() ?>)"><?= $translator->trans('add_to_cart') ?></button>
                </div>
            </div>
            <div style="margin-top: 40px;">
                <h2>Mô tả chi tiết</h2>
                <div class="content">
                    <?= nl2br(htmlspecialchars($product->getDescription())) ?>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 TuzyCMS. All rights reserved.</p>
        </div>
    </footer>

    <script>
    function addToCart(productId) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const existing = cart.find(item => item.productId === productId);
        if (existing) {
            existing.quantity++;
        } else {
            cart.push({productId: productId, quantity: 1});
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        alert('Đã thêm vào giỏ hàng!');
    }
    </script>
</body>
</html>

