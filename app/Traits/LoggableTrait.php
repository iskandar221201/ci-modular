<?php

namespace App\Traits;

trait LoggableTrait
{
    /**
     * Build a JSON-encoded log payload string.
     *
     * If $e is provided, an 'exception' key is added to the payload.
     * If json_encode fails (e.g. non-serializable context), a fallback
     * JSON string is returned so logging never crashes the application.
     *
     * @param string          $level   Log level (e.g. 'INFO', 'WARNING', 'ERROR').
     * @param string          $action  A short label describing the action being logged.
     * @param array           $context Additional key-value data to include in the payload.
     * @param \Throwable|null $e       Optional exception to attach to the payload.
     */
    private function buildLogPayload(string $level, string $action, array $context = [], ?\Throwable $e = null): string
    {
        $payload = [
            'timestamp' => date('c'),
            'level'     => strtoupper($level),
            'action'    => $action,
            'user_id'   => function_exists('auth') && auth()->loggedIn() ? auth()->id() : null,
            'ip'        => service('request')->getIPAddress(),
            'context'   => $context,
        ];

        if ($e !== null) {
            $payload['exception'] = [
                'class'   => get_class($e),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ];
        }

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            return json_encode([
                'timestamp' => date('c'),
                'level'     => strtoupper($level),
                'action'    => $action,
                'error'     => 'context_not_serializable',
            ]);
        }

        return $json;
    }

    /**
     * Log an informational message.
     *
     * @param string $action  A short label describing the action being logged.
     * @param array  $context Additional key-value data to include in the payload.
     *
     * @warning Do NOT pass sensitive data (PII, tokens, passwords) in $context.
     */
    protected function logInfo(string $action, array $context = []): void
    {
        log_message('info', $this->buildLogPayload('INFO', $action, $context));
    }

    /**
     * Log a warning message.
     *
     * @param string $action  A short label describing the action being logged.
     * @param array  $context Additional key-value data to include in the payload.
     *
     * @warning Do NOT pass sensitive data (PII, tokens, passwords) in $context.
     */
    protected function logWarning(string $action, array $context = []): void
    {
        log_message('warning', $this->buildLogPayload('WARNING', $action, $context));
    }

    /**
     * Log an error message. Pass $e to include exception details in the payload.
     *
     * @param string          $action  A short label describing the action being logged.
     * @param array           $context Additional key-value data to include in the payload.
     * @param \Throwable|null $e       Optional exception to attach to the payload.
     *
     * @warning Do NOT pass sensitive data (PII, tokens, passwords) in $context.
     */
    protected function logError(string $action, array $context = [], ?\Throwable $e = null): void
    {
        log_message('error', $this->buildLogPayload('ERROR', $action, $context, $e));
    }
}
