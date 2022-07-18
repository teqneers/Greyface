<?php

namespace App\Test;

use App\Domain\Entity\User\User;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Symfony\Component\Mime\MimeTypes;
use Webmozart\Assert\Assert;

trait ApiTestTrait
{
    use WebTestTrait;

    public static function createApiClient(User $user, array $options = [], array $server = []): KernelBrowser
    {
        return static::createAuthenticatedClient(
            $user,
            $options,
            array_merge(
                $server,
                [
                    'HTTP_ACCEPT' => 'application/json',
                ]
            )
        );
    }

    public static function assertIsSuccessfulJsonResponse(): void
    {
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public static function getSuccessfulJsonResponse(KernelBrowser $browser)
    {
        self::assertIsSuccessfulJsonResponse();
        $response = $browser->getResponse();
        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    public static function sendApiJsonRequest(
        KernelBrowser $browser,
        string $method,
        string $url,
        $payload
    ): Crawler {
        return $browser->request(
            $method,
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    public static function sendApiUploadRequest(
        HttpKernelBrowser $browser,
        string $url,
        SplFileInfo $file,
        ?string $originalName = null,
        ?string $mimeType = null,
        string $method = 'POST',
        string $key = 'file'
    ): Crawler {
        Assert::fileExists($file->getPathname());
        Assert::readable($file->getPathname());
        return $browser->request(
            $method,
            $url,
            [],
            [
                $key => new UploadedFile(
                    $file->getPathname(),
                    $originalName ?? $file->getBasename(),
                    $mimeType ?? MimeTypes::getDefault()->guessMimeType($file) ?? 'application/octet-stream',
                    null,
                    true
                ),
            ],
            [
                'CONTENT_TYPE' => 'multipart/form-data',
            ]
        );
    }
}
