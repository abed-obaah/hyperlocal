<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends push notifications through Expo's push service.
 * https://docs.expo.dev/push-notifications/sending-notifications/
 *
 * Delivery is best-effort: any transport error is logged and swallowed so that
 * a push failure can never break the order/delivery flow that triggered it.
 */
class ExpoPushService
{
    private const ENDPOINT = 'https://exp.host/--/api/v2/push/send';

    /**
     * @param  string[]  $tokens  Expo push tokens (e.g. "ExponentPushToken[xxx]")
     */
    public function send(array $tokens, string $title, string $body, array $data = []): void
    {
        $tokens = array_values(array_filter($tokens));
        if (empty($tokens)) {
            return;
        }

        $messages = array_map(fn (string $to) => [
            'to' => $to,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'sound' => 'default',
        ], $tokens);

        try {
            $response = Http::acceptJson()
                ->timeout(5)
                ->post(self::ENDPOINT, $messages);

            if ($response->failed()) {
                Log::warning('Expo push send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Expo push send threw', ['message' => $e->getMessage()]);
        }
    }
}
