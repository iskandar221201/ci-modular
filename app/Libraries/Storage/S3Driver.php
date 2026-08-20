<?php

namespace App\Libraries\Storage;

use App\Contracts\StorageDriverInterface;

class S3Driver implements StorageDriverInterface
{
    protected string $bucket;
    protected string $region;
    protected string $key;
    protected string $secret;
    protected ?string $endpoint;

    public function __construct(array $config)
    {
        foreach (['bucket', 'region', 'key', 'secret'] as $requiredKey) {
            if (! isset($config[$requiredKey]) || ! is_string($config[$requiredKey]) || $config[$requiredKey] === '') {
                throw new \InvalidArgumentException('S3Driver config missing required key: ' . $requiredKey);
            }
        }

        $this->bucket = $config['bucket'];
        $this->region = $config['region'];
        $this->key = $config['key'];
        $this->secret = $config['secret'];
        $this->endpoint = isset($config['endpoint']) && is_string($config['endpoint']) && $config['endpoint'] !== ''
            ? rtrim($config['endpoint'], '/')
            : null;
    }

    public function put(string $relativePath, string $content): bool
    {
        $url = $this->buildUrl($relativePath);
        $headers = $this->sign('PUT', $url, $content, ['content-type' => 'application/octet-stream']);

        $result = $this->retryRequest(function () use ($url, $content, $headers): array {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $error = curl_error($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return ['response' => $response, 'error' => $error, 'status' => $status];
        }, 'S3 upload failed');

        if ($result['status'] >= 200 && $result['status'] < 300) {
            return true;
        }

        throw new \RuntimeException('S3 upload failed with HTTP ' . $result['status']);
    }

    public function delete(string $relativePath): bool
    {
        $url = $this->buildUrl($relativePath);
        $headers = $this->sign('DELETE', $url, '', []);

        $result = $this->retryRequest(function () use ($url, $headers): array {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $error = curl_error($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return ['response' => $response, 'error' => $error, 'status' => $status];
        }, 'S3 delete failed');

        if ($result['status'] === 204 || $result['status'] === 404) {
            return true;
        }

        log_message('error', '[S3Driver] Delete failed with HTTP ' . $result['status'] . ' for path: ' . $relativePath);
        return false;
    }

    public function url(string $relativePath): string
    {
        $path = ltrim($relativePath, '/');

        if ($this->endpoint !== null) {
            return $this->endpoint . '/' . $path;
        }

        return 'https://' . $this->bucket . '.s3.' . $this->region . '.amazonaws.com/' . $path;
    }

    protected function buildUrl(string $relativePath): string
    {
        $path = $this->encodePath($relativePath);

        if ($this->endpoint !== null) {
            return $this->endpoint . '/' . $path;
        }

        return 'https://' . $this->bucket . '.s3.' . $this->region . '.amazonaws.com/' . $path;
    }

    protected function encodePath(string $relativePath): string
    {
        $trimmed = ltrim($relativePath, '/');
        $segments = explode('/', $trimmed);
        $encodedSegments = [];

        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }

            $encodedSegments[] = rawurlencode($segment);
        }

        return implode('/', $encodedSegments);
    }

    /**
     * @param callable(): array{response: string|false, error: string, status: int} $callback
     * @return array{response: string|false, error: string, status: int}
     */
    private function retryRequest(callable $callback, string $errorPrefix): array
    {
        $maxAttempts = 3;
        $delayMs = 100;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $result = $callback();

            $isTransient = ($result['response'] === false || $result['error'] !== '')
                || ($result['status'] >= 500 && $result['status'] < 600);

            if (!$isTransient) {
                return $result;
            }

            if ($attempt < $maxAttempts) {
                log_message('warning', sprintf(
                    '[S3Driver] %s (attempt %d/%d): retrying in %dms',
                    $errorPrefix,
                    $attempt,
                    $maxAttempts,
                    $delayMs,
                ));

                usleep($delayMs * 1000);
                $delayMs *= 2;
            }
        }

        if ($result['error'] !== '') {
            throw new \RuntimeException($errorPrefix . ': ' . $result['error']);
        }

        return $result;
    }

    private function sign(string $method, string $url, string $body, array $headers): array
    {
        $parsedUrl = parse_url($url);
        $timestamp = gmdate('Ymd\THis\Z');
        $dateStamp = gmdate('Ymd');
        $payloadHash = hash('sha256', $body);

        $requestHeaders = [
            'host' => $parsedUrl['host'] ?? '',
            'x-amz-date' => $timestamp,
            'x-amz-content-sha256' => $payloadHash,
        ];

        foreach ($headers as $name => $value) {
            $requestHeaders[strtolower($name)] = trim((string) $value);
        }

        ksort($requestHeaders);

        $canonicalHeaderLines = [];
        foreach ($requestHeaders as $name => $value) {
            $canonicalHeaderLines[] = $name . ':' . preg_replace('/\s+/', ' ', $value);
        }

        $canonicalHeaderString = implode("\n", $canonicalHeaderLines) . "\n";
        $signedHeaders = implode(';', array_keys($requestHeaders));
        $canonicalRequest = implode("\n", [
            $method,
            $this->canonicalUri($parsedUrl['path'] ?? '/'),
            $this->canonicalQuery($parsedUrl['query'] ?? ''),
            $canonicalHeaderString,
            $signedHeaders,
            $payloadHash,
        ]);

        $credentialScope = $dateStamp . '/' . $this->region . '/s3/aws4_request';
        $stringToSign = implode("\n", [
            'AWS4-HMAC-SHA256',
            $timestamp,
            $credentialScope,
            hash('sha256', $canonicalRequest),
        ]);

        $signingKey = $this->buildSigningKey($dateStamp);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);
        $authorization = 'AWS4-HMAC-SHA256 Credential=' . $this->key . '/' . $credentialScope . ', SignedHeaders=' . $signedHeaders . ', Signature=' . $signature;

        $result = [];
        foreach ($requestHeaders as $name => $value) {
            $result[] = $name . ': ' . $value;
        }
        $result[] = 'Authorization: ' . $authorization;

        return $result;
    }

    protected function canonicalUri(string $path): string
    {
        $normalizedPath = '/' . ltrim($path, '/');
        $segments = explode('/', $normalizedPath);
        $encodedSegments = [];

        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }

            $encodedSegments[] = rawurlencode($segment);
        }

        return '/' . implode('/', $encodedSegments);
    }

    protected function canonicalQuery(string $query): string
    {
        return $query;
    }

    protected function buildSigningKey(string $dateStamp): string
    {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $this->secret, true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);

        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }
}
