<?php

namespace App\Libraries;

use App\Traits\LoggableTrait;

/**
 * AppLogger — static facade for LoggableTrait.
 *
 * All log payload building logic (buildLogPayload) lives in LoggableTrait.
 * This class is a thin wrapper that allows logging to be called statically
 * from anywhere without needing to inject the trait or extend a specific class.
 *
 * Usage:
 *   AppLogger::info('payment.success', ['amount' => 50000]);
 *   AppLogger::warning('rate.limit.hit', ['ip' => $ip]);
 *   AppLogger::error('webhook.failed', ['payload' => $raw], $exception);
 */
class AppLogger
{
    use LoggableTrait;

    /** @var self|null Singleton instance shared across all static calls. */
    private static ?self $instance = null;

    /**
     * Return the singleton AppLogger instance.
     * The instance is shared to minimise instantiation overhead.
     */
    private static function getInstance(): self
    {
        if (static::$instance === null) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Log an informational message.
     *
     * @param string               $action  A short label describing the action being logged.
     * @param array<string, mixed> $context Additional key-value data to include in the payload.
     *
     * @warning Do NOT pass sensitive data (PII, tokens, passwords) in $context.
     */
    public static function info(string $action, array $context = []): void
    {
        static::getInstance()->logInfo($action, $context);
    }

    /**
     * Log a warning message.
     *
     * @param string               $action  A short label describing the action being logged.
     * @param array<string, mixed> $context Additional key-value data to include in the payload.
     *
     * @warning Do NOT pass sensitive data (PII, tokens, passwords) in $context.
     */
    public static function warning(string $action, array $context = []): void
    {
        static::getInstance()->logWarning($action, $context);
    }

    /**
     * Log an error message. Pass $e to include exception details in the payload.
     *
     * @param string               $action  A short label describing the action being logged.
     * @param array<string, mixed> $context Additional key-value data to include in the payload.
     * @param \Throwable|null      $e       Optional exception to attach to the payload.
     *
     * @warning Do NOT pass sensitive data (PII, tokens, passwords) in $context.
     */
    public static function error(string $action, array $context = [], ?\Throwable $e = null): void
    {
        static::getInstance()->logError($action, $context, $e);
    }
}
