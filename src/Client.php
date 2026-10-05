<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub;

use Gurmehub\Odemehub\Exception\AuthenticationException;
use Gurmehub\Odemehub\Exception\ForbiddenException;
use Gurmehub\Odemehub\Exception\NotFoundException;
use Gurmehub\Odemehub\Exception\RateLimitException;
use Gurmehub\Odemehub\Exception\SignatureException;
use Gurmehub\Odemehub\Exception\TransportException;
use Gurmehub\Odemehub\Exception\UnexpectedResponseException;
use Gurmehub\Odemehub\Exception\ValidationException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

/**
 * The gateway, as this application talks to it. Every request leaves signed
 * with the team's secret and every answer is checked against it, so both
 * sides can tell the other really is who it says it is.
 *
 * There is one method per endpoint, named after it: `create-order` is
 * `createOrder()`, and takes a `Request\CreateOrder`.
 */
final class Client
{
    private readonly ClientInterface $http;

    private readonly Signature $signature;

    public function __construct(
        private readonly Options $options,
        ?ClientInterface $http = null,
    ) {
        $this->http = $http ?? new GuzzleClient(['timeout' => 60]);
        $this->signature = new Signature($options->apiSecret);
    }

    /**
     * Start a payment the customer confirms with their bank. A successful
     * answer is not a settled payment: the customer still has to be sent
     * to the address it comes back with, and `retrievePayments` says what
     * became of it once they are back.
     */
    public function securePayment(Request\SecurePayment $payment): Response\SecurePayment
    {
        return Response\SecurePayment::fromArray($this->send($payment));
    }

    /**
     * Charge a payment straight to the card. A successful answer is a
     * settled payment.
     */
    public function regularPayment(Request\RegularPayment $payment): Response\Payment
    {
        return Response\Payment::fromArray($this->send($payment));
    }

    /**
     * Give money back out of a payment the provider has settled, whole or
     * in part.
     */
    public function refundPayment(Request\RefundPayment $refund): Response\GiveBack
    {
        return Response\GiveBack::fromArray($this->send($refund));
    }

    /**
     * Take back the whole of a payment the provider has not settled yet.
     */
    public function cancelPayment(Request\CancelPayment $cancel): Response\GiveBack
    {
        return Response\GiveBack::fromArray($this->send($cancel));
    }

    /**
     * Payments as they stand — by token, every attempt under one of the
     * merchant's own references, or the ones made between two days — each
     * with its state, amount and what became of its money.
     */
    public function retrievePayments(Request\RetrievePayments $payments): Response\PaymentList
    {
        return Response\PaymentList::fromArray($this->send($payments));
    }

    /**
     * Ask what is known about a card from the head of its number, and how
     * the amount may be paid off on it. Nothing is charged and nothing is
     * written down.
     */
    public function retrieveBin(Request\RetrieveBin $bin): Response\Bin
    {
        return Response\Bin::fromArray($this->send($bin));
    }

    /**
     * Open an order to be paid on the gateway's own page, or overwrite the
     * open one already under the same reference. Nothing is charged here;
     * the customer is sent to the address that comes back and pays there.
     */
    public function createOrder(Request\CreateOrder $order): Response\OrderDetails
    {
        return Response\OrderDetails::fromArray($this->send($order));
    }

    /**
     * Orders as they stand — by token, by the merchant's own reference, or
     * the ones opened between two days — each with its customer.
     */
    public function retrieveOrders(Request\RetrieveOrders $orders): Response\OrderList
    {
        return Response\OrderList::fromArray($this->send($orders));
    }

    /**
     * Change an open order. Only what is sent is written.
     */
    public function updateOrder(Request\UpdateOrder $order): Response\OrderDetails
    {
        return Response\OrderDetails::fromArray($this->send($order));
    }

    /**
     * Open a payment link, or overwrite the one already under the same
     * reference. The address that comes back is the link itself.
     */
    public function createPaymentLink(Request\CreatePaymentLink $link): Response\PaymentLinkDetails
    {
        return Response\PaymentLinkDetails::fromArray($this->send($link));
    }

    /**
     * Payment links as they stand, each with the latest fifty payment
     * attempts made on it and how many there have been in all.
     */
    public function retrievePaymentLinks(Request\RetrievePaymentLinks $links): Response\PaymentLinkList
    {
        return Response\PaymentLinkList::fromArray($this->send($links));
    }

    /**
     * Change a payment link: its lines, its last day, whether it takes
     * payments. Only what is sent is written.
     */
    public function updatePaymentLink(Request\UpdatePaymentLink $link): Response\PaymentLinkDetails
    {
        return Response\PaymentLinkDetails::fromArray($this->send($link));
    }

    /**
     * Open a subscription, its first renewal to be paid on the gateway's
     * own page and the rest taken from the card kept then; or overwrite
     * the one already under the same reference while nothing has been
     * paid on it.
     */
    public function createSubscription(Request\CreateSubscription $subscription): Response\SubscriptionDetails
    {
        return Response\SubscriptionDetails::fromArray($this->send($subscription));
    }

    /**
     * Subscriptions as they stand, each with its customer and the renewal
     * it is on.
     */
    public function retrieveSubscriptions(Request\RetrieveSubscriptions $subscriptions): Response\SubscriptionList
    {
        return Response\SubscriptionList::fromArray($this->send($subscriptions));
    }

    /**
     * Change a subscription, or call it off with the status `cancelled`.
     * Only what is sent is written. Nothing is given back on a
     * cancellation: the customer is served to the end of what they paid
     * for, and nothing is charged after that.
     */
    public function updateSubscription(Request\UpdateSubscription $subscription): Response\SubscriptionDetails
    {
        return Response\SubscriptionDetails::fromArray($this->send($subscription));
    }

    /**
     * Keep a card for a customer without making a payment on it.
     */
    public function createSavedCard(Request\CreateSavedCard $savedCard): Response\SavedCardDetails
    {
        return Response\SavedCardDetails::fromArray($this->send($savedCard));
    }

    /**
     * Kept cards — by token, every card of a customer by their reference,
     * or the ones kept between two days — each with its customer, the
     * default first.
     */
    public function retrieveSavedCards(Request\RetrieveSavedCards $savedCards): Response\SavedCardList
    {
        return Response\SavedCardList::fromArray($this->send($savedCards));
    }

    /**
     * Make a kept card the one its customer pays with by default.
     */
    public function updateSavedCard(Request\UpdateSavedCard $savedCard): Response\SavedCardDetails
    {
        return Response\SavedCardDetails::fromArray($this->send($savedCard));
    }

    /**
     * Let go of a kept card, at the provider and with the gateway.
     */
    public function deleteSavedCard(Request\DeleteSavedCard $savedCard): Response\DeletedSavedCard
    {
        return Response\DeletedSavedCard::fromArray($this->send($savedCard));
    }

    /**
     * Read a word the gateway posted to one of the merchant's webhook
     * addresses. Hand it the request exactly as it arrived — the method,
     * the path of the address it came to (without the query string), the
     * raw body and the two headers — and nothing in it is believed until
     * the signature is checked against the secret.
     *
     *     $webhook = $client->webhook(
     *         $_SERVER['REQUEST_METHOD'],
     *         parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
     *         file_get_contents('php://input'),
     *         $_SERVER['HTTP_X_TIMESTAMP'] ?? null,
     *         $_SERVER['HTTP_X_SIGNATURE'] ?? null,
     *     );
     *
     * The word only names what it is about; ask the gateway what became
     * of it before acting on it. Answer the gateway with any 2xx once the
     * word is taken; it tries again, up to five times, until it hears one.
     *
     * @throws SignatureException
     */
    public function webhook(string $method, string $path, string $body, ?string $timestamp, ?string $signature): Response\Webhook
    {
        if (! $this->verifyWebhook($method, $path, $body, $timestamp, $signature)) {
            throw new SignatureException('Bildirimin imzası doğrulanamadı; bildirim ödeme geçidinden gelmemiş olabilir.');
        }

        return Response\Webhook::fromArray($this->decode($body, 0));
    }

    /**
     * Whether a word that arrived at a webhook address was signed by the
     * gateway with this team's secret, recently enough to be taken. The
     * path is the address's own, with its leading slash and without the
     * query string; the body is the raw text, byte for byte.
     */
    public function verifyWebhook(string $method, string $path, string $body, ?string $timestamp, ?string $signature): bool
    {
        return $this->signature->verify($method, $path, $body, $timestamp, $signature);
    }

    /**
     * Sign what is being asked for, hand it to the gateway and read the
     * answer back. The body is signed exactly as it is sent, character for
     * character, so it is written once and used for both.
     *
     * @return array<string, mixed>
     */
    private function send(Request\Message $message): array
    {
        $method = $message->method();
        $path = $this->options->path($message->path());
        $body = $this->encode($message);

        $headers = [
            Options::API_KEY_HEADER => $this->options->apiKey,
            ...$this->signature->headers($method, $path, $body),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        try {
            $response = $this->http->request($method, $this->options->url($message->path()), [
                'headers' => $headers,
                'body' => $body,
                'http_errors' => false,
            ]);
        } catch (GuzzleException $exception) {
            throw new TransportException('Ödeme geçidine ulaşılamadı: '.$exception->getMessage(), previous: $exception);
        }

        return $this->read($response, $method, $path);
    }

    /**
     * The body, written once. A body with nothing in it — asking after the
     * latest records — is still an object, never an empty list.
     */
    private function encode(Request\Message $message): string
    {
        $body = $message->toArray();

        try {
            return json_encode($body === [] ? new \stdClass : $body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new UnexpectedResponseException('İstek gövdesi JSON olarak yazılamadı: '.$exception->getMessage(), 0);
        }
    }

    /**
     * Read the answer. An outcome is answered with 200 and signed, however
     * the payment itself turned out: a payment the provider declined is an
     * outcome like any other and comes back rather than being raised. The
     * signature is checked over the method and path of the request and
     * the answer's own moment and body.
     *
     * Anything else is a refusal — the request never became a payment — and
     * the status says which kind. The gateway signs some of those too, but
     * a signature does not make a refusal an outcome, so the status is read
     * first.
     *
     * @return array<string, mixed>
     */
    private function read(ResponseInterface $response, string $method, string $path): array
    {
        $status = $response->getStatusCode();
        $payload = (string) $response->getBody();

        if ($status === 200) {
            $verified = $this->signature->verify(
                $method,
                $path,
                $payload,
                $this->header($response, Signature::TIMESTAMP_HEADER),
                $this->header($response, Signature::HEADER),
            );

            if (! $verified) {
                throw new SignatureException('Yanıtın imzası doğrulanamadı; yanıt ödeme geçidinden gelmemiş olabilir.');
            }

            return $this->decode($payload, $status);
        }

        [$message, $errors] = $this->refusal($payload);

        throw match ($status) {
            401 => new AuthenticationException($message),
            403 => new ForbiddenException($message),
            404 => new NotFoundException($message),
            422 => new ValidationException($message, $errors),
            429 => new RateLimitException($message, $this->retryAfter($response)),
            default => new UnexpectedResponseException($message, $status),
        };
    }

    /**
     * A header, or nothing when the answer did not carry it.
     */
    private function header(ResponseInterface $response, string $name): ?string
    {
        $value = $response->getHeaderLine($name);

        return $value === '' ? null : $value;
    }

    /**
     * How long the gateway asked to wait before trying again, in seconds.
     */
    private function retryAfter(ResponseInterface $response): ?int
    {
        $value = $this->header($response, 'Retry-After');

        return $value !== null && preg_match('/^[0-9]+$/', $value) === 1 ? (int) $value : null;
    }

    /**
     * What a refusal says. The gateway answers in the one shape it answers
     * everything in, so what went wrong and which fields it was about are
     * found under `result`; an answer that never reached it, such as one
     * from a proxy in front of it, may say it under `message`.
     *
     * @return array{0: string, 1: array<string, list<string>>}
     */
    private function refusal(string $payload): array
    {
        $body = json_decode($payload, true);
        $result = is_array($body) && is_array($body['result'] ?? null) ? $body['result'] : [];

        $message = match (true) {
            is_string($result['message'] ?? null) => $result['message'],
            is_array($body) && is_string($body['message'] ?? null) => $body['message'],
            default => 'Ödeme geçidi isteği reddetti.',
        };

        $errors = match (true) {
            is_array($result['errors'] ?? null) => $result['errors'],
            is_array($body) && is_array($body['errors'] ?? null) => $body['errors'],
            default => [],
        };

        return [$message, $errors];
    }

    /**
     * Read a body the signature has already vouched for.
     *
     * @return array<string, mixed>
     */
    private function decode(string $payload, int $status): array
    {
        $body = json_decode($payload, true);

        if (! is_array($body)) {
            throw new UnexpectedResponseException("Ödeme geçidi {$status} durumuyla okunamayan bir yanıt döndü.", $status);
        }

        return $body;
    }
}
