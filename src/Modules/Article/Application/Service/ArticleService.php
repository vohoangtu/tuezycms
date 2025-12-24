<?php

declare(strict_types=1);

namespace Modules\Article\Application\Service;

use Modules\Article\Domain\Model\Article;
use Modules\Article\Domain\Model\ArticleType;
use Modules\Article\Infrastructure\Repository\ArticleRepository;
use Modules\Article\Infrastructure\Repository\ArticleTypeRepository;

class ArticleService
{
    private ArticleRepository $articleRepository;
    private ArticleTypeRepository $typeRepository;

    public function __construct(
        ArticleRepository $articleRepository,
        ArticleTypeRepository $typeRepository
    ) {
        $this->articleRepository = $articleRepository;
        $this->typeRepository = $typeRepository;
    }

    public function createArticle(
        string $title,
        string $slug,
        string $content,
        int $typeId,
        string $status = 'draft',
        ?int $authorId = null
    ): Article {
        $type = $this->typeRepository->findById($typeId);
        if ($type === null) {
            throw new \RuntimeException('Article type not found.');
        }

        $article = new Article($title, $slug, $content, $type, $status);
        $article->setAuthorId($authorId);
        
        $this->articleRepository->save($article);
        
        return $article;
    }

    public function updateArticle(
        int $id,
        string $title,
        string $slug,
        string $content,
        int $typeId,
        string $status
    ): Article {
        $article = $this->articleRepository->findById($id);
        if ($article === null) {
            throw new \RuntimeException('Article not found.');
        }

        $type = $this->typeRepository->findById($typeId);
        if ($type === null) {
            throw new \RuntimeException('Article type not found.');
        }

        $article->setTitle($title);
        $article->setSlug($slug);
        $article->setContent($content);
        $article->setType($type);
        $article->setStatus($status);

        $this->articleRepository->save($article);
        
        return $article;
    }

    public function getArticle(int $id): ?Article
    {
        return $this->articleRepository->findById($id);
    }

    public function getArticleBySlug(string $slug, string $locale = 'vi'): ?Article
    {
        return $this->articleRepository->findBySlug($slug, $locale);
    }

    public function listArticles(int $limit = 100, int $offset = 0, string $locale = 'vi'): array
    {
        return $this->articleRepository->findAll($limit, $offset, $locale);
    }

    public function listArticlesByType(int $typeId, int $limit = 100, int $offset = 0): array
    {
        return $this->articleRepository->findByType($typeId, $limit, $offset);
    }

    public function listArticlesByTypeSlug(string $typeSlug, int $limit = 100, int $offset = 0, string $locale = 'vi'): array
    {
        return $this->articleRepository->findByTypeSlug($typeSlug, $limit, $offset, $locale);
    }

    public function deleteArticle(int $id): void
    {
        $this->articleRepository->delete($id);
    }

    public function createArticleType(
        string $name,
        string $slug,
        string $description = ''
    ): ArticleType {
        $type = new ArticleType($name, $slug, $description);
        $this->typeRepository->save($type);
        return $type;
    }

    public function listArticleTypes(): array
    {
        return $this->typeRepository->findAll();
    }

    public function listActiveArticleTypes(): array
    {
        return $this->typeRepository->findActive();
    }
}

