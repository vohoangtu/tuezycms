<?php

declare(strict_types=1);

use Core\Container\ServiceContainer;
use Core\Routing\RouteRegistry;

/** @var ServiceContainer $container */
$router = $container->make(RouteRegistry::class);

// Define Controllers (Resolution happens at dispatch time, but we use class names)
use Modules\Home\Presentation\Controller\HomeController;
use Modules\Page\Presentation\Controller\PageViewController;
use Modules\Article\Presentation\Controller\ArticleViewController;
use Modules\Product\Presentation\Controller\ProductViewController;
use Shared\Presentation\Controller\ContentViewController;

// --- Home Route ---
$router->get('', [HomeController::class, 'index'])->name('home');

// --- Page Routes ---
$router->get('lien-he', [PageViewController::class, 'contact'])->name('contact.vi');
$router->get('contact', [PageViewController::class, 'contact'])->name('contact.en');


// --- Article Type Routes (Listing) ---
$articleTypes = ['tin-tuc', 'news', 'dich-vu', 'services', 'kien-thuc', 'knowledge'];
foreach ($articleTypes as $type) {
    $router->get($type, [ArticleViewController::class, 'index'])->name('article.type.' . $type);
}

// --- Product Routes (Listing) ---
$router->get('san-pham', [ProductViewController::class, 'index'])->name('products.vi');
$router->get('products', [ProductViewController::class, 'index'])->name('products.en');


// --- Detail Routes (Dynamic) ---

// Articles Detail: (type)/(slug)
$router->get('{type}/{slug}', [ArticleViewController::class, 'show'])->name('article.detail'); 

// --- Content Fallback Route (Product -> Article) ---
$router->get('{slug}', [ContentViewController::class, 'show'])->name('content.detail');
