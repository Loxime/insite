<?php

namespace App\Service;

use Aws\Exception\AwsException;
use Aws\Result;
use Aws\S3\S3Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class MinioStorage
{
    private S3Client $client;

    public function __construct(
        #[Autowire('%env(MINIO_ENDPOINT)%')]
        string $endpoint,

        #[Autowire('%env(MINIO_REGION)%')]
        string $region,

        #[Autowire('%env(MINIO_BUCKET)%')]
        private readonly string $bucket,

        #[Autowire('%env(MINIO_ROOT_USER)%')]
        string $accessKey,

        #[Autowire('%env(MINIO_ROOT_PASSWORD)%')]
        string $secretKey,
    ) {
        $this->client = new S3Client([
            'version' => 'latest',
            'region' => $region,
            'endpoint' => $endpoint,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $accessKey,
                'secret' => $secretKey,
            ],
        ]);
    }

    public function ensureBucketExists(): void
    {
        try {
            $this->client->headBucket([
                'Bucket' => $this->bucket,
            ]);

            return;
        } catch (AwsException $exception) {
            if (
                $exception->getStatusCode() !== 404
                && $exception->getAwsErrorCode() !== 'NoSuchBucket'
            ) {
                throw $exception;
            }
        }

        $this->client->createBucket([
            'Bucket' => $this->bucket,
        ]);
    }

    public function uploadBlogImage(
        UploadedFile $file,
    ): string {
        return $this->uploadImage(
            $file,
            'blog',
        );
    }

    public function uploadGameImage(
        UploadedFile $file,
    ): string {
        return $this->uploadImage(
            $file,
            'games',
        );
    }

    public function uploadGameSectionImage(
        UploadedFile $file,
    ): string {
        return $this->uploadImage(
            $file,
            'games/sections',
        );
    }

    public function uploadAboutImage(
        UploadedFile $file,
    ): string {
        return $this->uploadImage(
            $file,
            'about',
        );
    }

    public function uploadAnnouncementImage(
        UploadedFile $file,
    ): string {
        return $this->uploadImage($file, 'announcements');
    }

    public function uploadAboutProfileImage(
        UploadedFile $file,
    ): string {
        return $this->uploadImage($file, 'about/profiles');
    }

    private function uploadImage(
        UploadedFile $file,
        string $directory,
    ): string {
        $this->ensureBucketExists();

        $mimeType = $file->getMimeType()
            ?? 'application/octet-stream';

        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => $file->guessExtension() ?: 'bin',
        };

        $key = sprintf(
            '%s/%s/%s.%s',
            $directory,
            (new \DateTimeImmutable())->format('Y/m'),
            bin2hex(random_bytes(16)),
            $extension,
        );

        $stream = fopen(
            $file->getPathname(),
            'rb',
        );

        if ($stream === false) {
            throw new \RuntimeException(
                'Impossible de lire le fichier image.',
            );
        }

        try {
            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => $stream,
                'ContentType' => $mimeType,
                'CacheControl' => (
                    'public, max-age=31536000, immutable'
                ),
            ]);
        } finally {
            fclose($stream);
        }

        return $key;
    }

    public function get(string $key): ?Result
    {
        try {
            return $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
        } catch (AwsException $exception) {
            if (
                $exception->getStatusCode() === 404
                || in_array(
                    $exception->getAwsErrorCode(),
                    ['NoSuchKey', 'NoSuchBucket'],
                    true,
                )
            ) {
                return null;
            }

            throw $exception;
        }
    }

    public function delete(?string $key): void
    {
        if ($key === null || $key === '') {
            return;
        }

        $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);
    }
}
