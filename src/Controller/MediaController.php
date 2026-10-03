<?php

namespace App\Controller;

use App\Service\MinioStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

final class MediaController extends AbstractController
{
    #[Route(
        '/media/{key}',
        name: 'media_show',
        methods: ['GET'],
        requirements: [
            'key' => '.+',
        ],
    )]
    public function show(
        string $key,
        MinioStorage $storage,
    ): Response {
        if (
            !str_starts_with($key, 'blog/')
            && !str_starts_with($key, 'games/')
            && !str_starts_with($key, 'about/')
        ) {
            throw $this->createNotFoundException();
        }

        $object = $storage->get($key);

        if ($object === null) {
            throw $this->createNotFoundException();
        }

        $body = $object['Body'];

        $headers = [
            'Content-Type' => (string) (
                $object['ContentType']
                ?? 'application/octet-stream'
            ),
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if (isset($object['ContentLength'])) {
            $headers['Content-Length'] = (string) $object['ContentLength'];
        }

        if (isset($object['ETag'])) {
            $headers['ETag'] = (string) $object['ETag'];
        }

        return new StreamedResponse(
            static function () use ($body): void {
                while (!$body->eof()) {
                    echo $body->read(8192);
                }
            },
            Response::HTTP_OK,
            $headers,
        );
    }
}
