<!DOCTYPE html>
<html lang="<?= $router->getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page['meta_title'] ?: $page['title']) ?> - TuzyCMS</title>
    <?php if ($page['meta_description']): ?>
    <meta name="description" content="<?= htmlspecialchars($page['meta_description']) ?>">
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
                        <a href="<?= $router->getLocale() === 'vi' ? $router->url('lien-he', 'en') : $router->url('lien-he', 'vi') ?>">
                            <?= $router->getLocale() === 'vi' ? 'EN' : 'VI' ?>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <article class="container" style="max-width: 800px; margin: 40px auto; padding: 20px;">
            <h1><?= htmlspecialchars($page['title']) ?></h1>
            <div class="content" style="margin-top: 20px;">
                <?= nl2br(htmlspecialchars($page['content'])) ?>
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

