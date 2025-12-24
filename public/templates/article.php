<!DOCTYPE html>
<html lang="<?= $router->getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article->getMetaTitle() ?: $article->getTitle()) ?> - TuzyCMS</title>
    <?php if ($article->getMetaDescription()): ?>
    <meta name="description" content="<?= htmlspecialchars($article->getMetaDescription()) ?>">
    <?php endif; ?>
    <?php if ($article->getMetaKeywords()): ?>
    <meta name="keywords" content="<?= htmlspecialchars($article->getMetaKeywords()) ?>">
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
                        <a href="<?= $router->getLocale() === 'vi' ? $router->url($article->getSlug(), 'en') : $router->url($article->getSlug(), 'vi') ?>">
                            <?= $router->getLocale() === 'vi' ? 'EN' : 'VI' ?>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <article class="container" style="max-width: 800px; margin: 40px auto; padding: 20px;">
            <h1><?= htmlspecialchars($article->getTitle()) ?></h1>
            <p class="meta">
                <?= htmlspecialchars($article->getType()->getName()) ?> • 
                <?= $article->getCreatedAt()->format('d/m/Y H:i') ?> • 
                <?= $article->getViews() ?> lượt xem
            </p>
            <?php if ($article->getFeaturedImage()): ?>
            <img src="<?= htmlspecialchars($article->getFeaturedImage()) ?>" alt="<?= htmlspecialchars($article->getTitle()) ?>" style="width: 100%; margin: 20px 0;">
            <?php endif; ?>
            <div class="content">
                <?= nl2br(htmlspecialchars($article->getContent())) ?>
            </div>
        </article>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 TuzyCMS. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

