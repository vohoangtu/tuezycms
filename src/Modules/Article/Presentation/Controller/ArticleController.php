<?php

declare(strict_types=1);

namespace Modules\Article\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Modules\Article\Application\Service\ArticleService;
use Shared\Infrastructure\Exception\NotFoundException;
use Shared\Infrastructure\Exception\BadRequestException;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;

class ArticleController extends BaseController
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

    public function index(): void
    {
        if ($this->request->method() !== 'GET') {
            throw new BadRequestException('Method not allowed');
        }

        $articles = $this->articleService->listArticles();
        $this->json($articles);
    }

    public function show(int $id): void
    {
        if ($this->request->method() !== 'GET') {
            throw new BadRequestException('Method not allowed');
        }

        $article = $this->articleService->getArticle($id);
        
        if ($article === null) {
            throw new NotFoundException('Article not found');
        }

        $this->json([
            'id' => $article->getId(),
            'title' => $article->getTitle(),
            'slug' => $article->getSlug(),
            'content' => $article->getContent(),
            'type_id' => $article->getType()->getId(),
            'status' => $article->getStatus(),
        ]);
    }

    public function store(): void
    {
        if ($this->request->method() !== 'POST') {
            throw new BadRequestException('Method not allowed');
        }

        $data = $this->request->input();

        if (isset($data['id']) && $data['id']) {
            $article = $this->articleService->updateArticle(
                (int)$data['id'],
                $data['title'],
                $data['slug'],
                $data['content'],
                (int)$data['type_id'],
                $data['status'] ?? 'draft'
            );
        } else {
            $article = $this->articleService->createArticle(
                $data['title'],
                $data['slug'],
                $data['content'],
                (int)$data['type_id'],
                $data['status'] ?? 'draft'
            );
        }

        $this->json(['success' => true, 'id' => $article->getId()]);
    }

    public function delete(int $id): void
    {
        if ($this->request->method() !== 'DELETE') {
            throw new BadRequestException('Method not allowed');
        }

        $this->articleService->deleteArticle($id);
        $this->json(['success' => true]);
    }
}
