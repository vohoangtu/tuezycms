<!DOCTYPE html>
<html lang="<?= $router->getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($translator->trans('news')) ?> - TuzyCMS</title>
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
            <h1><?= htmlspecialchars($translator->trans('news')) ?></h1>
            
            <div class="articles-grid" style="margin-top: 30px;">
                <?php foreach ($articles as $article): ?>
                <article class="article-card">
                    <?php if ($article->getFeaturedImage()): ?>
                    <img src="<?= htmlspecialchars($article->getFeaturedImage()) ?>" alt="<?= htmlspecialchars($article->getTitle()) ?>">
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($article->getTitle()) ?></h3>
                    <p class="meta">
                        <?= htmlspecialchars($article->getType()->getName()) ?> • 
                        <?= $article->getCreatedAt()->format('d/m/Y') ?>
                    </p>
                    <a href="<?= $router->url($article->getSlug()) ?>"><?= $translator->trans('read_more') ?> →</a>
                </article>
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

