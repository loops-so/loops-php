<?php

namespace Loops;

use Loops\LoopsClient;

class Uploads
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    private const EXTENSION_MIME_TYPES = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];

    private const MAX_BYTES = 4000000;

    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function upload(string $path): mixed
    {
        if (!is_readable($path)) {
            throw new \InvalidArgumentException(message: 'File not found or not readable: ' . $path);
        }

        $contents = file_get_contents(filename: $path);
        $content_length = strlen(string: $contents);

        if ($content_length === 0) {
            throw new \InvalidArgumentException(message: 'File is empty: ' . $path);
        }

        if ($content_length > self::MAX_BYTES) {
            throw new \InvalidArgumentException(message: 'File exceeds the maximum allowed size of 4,000,000 bytes.');
        }

        $content_type = $this->resolveContentType(path: $path);
        if ($content_type === null) {
            throw new \InvalidArgumentException(message: 'Unsupported image type. Supported types: JPEG, PNG, GIF, and WebP.');
        }

        $created = $this->client->query(method: 'POST', endpoint: 'v1/uploads', options: [
            'json' => [
                'contentType' => $content_type,
                'contentLength' => $content_length,
            ]
        ]);

        $response = $this->client->getUploadHttpClient()->put($created['presignedUrl'], [
            'headers' => [
                'Content-Type' => $content_type,
                'Content-Length' => (string) $content_length,
            ],
            'body' => $contents,
            'http_errors' => false,
        ]);

        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException(
                message: 'Failed to upload file to pre-signed URL. HTTP status: ' . $response->getStatusCode(),
                code: $response->getStatusCode()
            );
        }

        return $this->client->query(
            method: 'POST',
            endpoint: 'v1/uploads/' . $created['emailAssetId'] . '/complete'
        );
    }

    private function resolveContentType(string $path): ?string
    {
        $finfo = finfo_open(flags: FILEINFO_MIME_TYPE);
        $mime = finfo_file(finfo: $finfo, filename: $path);

        if (in_array(needle: $mime, haystack: self::ALLOWED_MIME_TYPES, strict: true)) {
            return $mime;
        }

        $extension = strtolower(string: pathinfo(path: $path, flags: PATHINFO_EXTENSION));

        return self::EXTENSION_MIME_TYPES[$extension] ?? null;
    }
}
