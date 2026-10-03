<?php

namespace App\Service;

use App\Entity\Post;
use App\Repository\PostRepository;

final class PostSlugger
{
    public function __construct(
        private readonly PostRepository $postRepository,
    ) {
    }

    public function generate(
        string $title,
        ?Post $currentPost = null,
    ): string {
        $slug = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $title,
        );

        if ($slug === false) {
            $slug = $title;
        }

        $slug = mb_strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'article';
        }

        $baseSlug = $slug;
        $suffix = 2;

        while (true) {
            $existingPost = $this->postRepository->findOneBy([
                'slug' => $slug,
            ]);

            if ($existingPost === null) {
                return $slug;
            }

            if (
                $currentPost !== null
                && $existingPost->getId() === $currentPost->getId()
            ) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $suffix;
            ++$suffix;
        }
    }
}
