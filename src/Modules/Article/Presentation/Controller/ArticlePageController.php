<?php

declare(strict_types=1);

namespace Modules\Article\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Modules\Article\Application\Service\ArticleService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;

class ArticlePageController extends BaseController
{
    private ArticleService $articleService;

    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        ArticleService $articleService
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
        $this->articleService = $articleService;
    }

    /**
     * Show articles page
     */
    public function index(): void
    {
        $articles = $this->articleService->listArticles();
        $types = $this->articleService->listArticleTypes();

        $this->render('admin/articles', [
            'articles' => $articles,
            'types' => $types
        ]);
    }
}
