<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub;

/**
 * How a merchant and the gateway vouch for each other's messages. A body
 * travels as plain JSON and, next to it, two headers: the moment it was
 * signed, in Unix seconds, and an HMAC-SHA256 under the merchant's secret
 * over that moment, the HTTP method, the path and the exact text of the
 * body, joined with newlines. The secret itself never travels; a message
 * whose signature does not match was not written by the holder of the
 * secret, or was changed on the way, and one signed more than a few
 * minutes ago is not taken either, so a copied message is worth nothing
 * for long.
 *
 * A request, its answer and a webhook are all signed the same way, so one
 * calculation checks every one of them:
 *
 *     hash_hmac('sha256', "{timestamp}\n{METHOD}\n{path}\n{body}", $apiSecret)
 *
 * The path is the one in the address, with its leading slash and without
 * the query string; the body is the raw text as sent, and the empty string
 * for a GET.
 *
 * Test vector: with the secret `secret_test`, at `1700000000`, a `POST` to
 * `/api/1000000001/gateway/regular-payment` with the body `{"a":1}` is
 * signed `4d6225c9dd46837418b40dd8140d76a24cd7520d81ff3b280bf98da8da6a8771`.
 */
final readonly class Signature
{
    public const ALGORITHM = 'sha256';

    /**
     * The header a signature travels in, both ways.
     */
    public const HEADER = 'X-Signature';

    /**
     * The header the moment of signing travels in, both ways.
     */
    public const TIMESTAMP_HEADER = 'X-Timestamp';

    /**
     * How far from now, either way, a signature's moment may lie and still
     * be taken, in seconds. The gateway allows the same.
     */
    public const TIMESTAMP_TOLERANCE = 300;

    public function __construct(private string $apiSecret) {}

    /**
     * The signature that vouches for a message: HMAC-SHA256 over the moment,
     * the method, the path and the body, written as lowercase hex.
     */
    public function sign(string $method, string $path, string $body, int $timestamp): string
    {
        return hash_hmac(self::ALGORITHM, self::signedText($method, $path, $body, $timestamp), $this->apiSecret);
    }

    /**
     * Whether a signature vouches for a message, and was made recently
     * enough to be taken.
     *
     * @param  string|null  $timestamp  The `X-Timestamp` header, as it arrived.
     * @param  string|null  $signature  The `X-Signature` header, as it arrived.
     */
    public function verify(string $method, string $path, string $body, ?string $timestamp, ?string $signature): bool
    {
        if (! is_string($timestamp) || preg_match('/^[0-9]+$/', $timestamp) !== 1 || abs(time() - (int) $timestamp) > self::TIMESTAMP_TOLERANCE) {
            return false;
        }

        return is_string($signature) && hash_equals($this->sign($method, $path, $body, (int) $timestamp), $signature);
    }

    /**
     * The two headers that vouch for a message going out, made for now.
     *
     * @return array<string, string>
     */
    public function headers(string $method, string $path, string $body): array
    {
        $timestamp = time();

        return [
            self::TIMESTAMP_HEADER => (string) $timestamp,
            self::HEADER => $this->sign($method, $path, $body, $timestamp),
        ];
    }

    /**
     * What the signature is taken over.
     */
    public static function signedText(string $method, string $path, string $body, int $timestamp): string
    {
        return implode("\n", [$timestamp, strtoupper($method), $path, $body]);
    }
}
