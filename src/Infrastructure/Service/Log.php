<?php

namespace App\Infrastructure\Service;

use Psr\Log\LoggerInterface;

class Log
{
    private static ?LoggerInterface $logger = null;

    public static function setLogger(?LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    public static function error(string $message, array $context = []): void
    {
        self::$logger?->error($message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::$logger?->info($message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::$logger?->debug($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::$logger?->warning($message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        self::$logger?->critical($message, $context);
    }

    public static function alert(string $message, array $context = []): void
    {
        self::$logger?->alert($message, $context);
    }

    public static function emergency(string $message, array $context = []): void
    {
        self::$logger?->emergency($message, $context);
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        self::$logger?->log($level, $message, $context);
    }

    public static function notice(string $message, array $context = []): void
    {
        self::$logger?->notice($message, $context);
    }

    /**
     * Extracts the base name of a class from a fully qualified class name.
     *
     * @param string $methodWithCompletePath The fully qualified class name and the method name (e.g., "App\\Infrastructure\\Service\\MyService::myMethod"), use the constant __METHOD__ to get it automatically.
     * @return string The base name of the class and the method.
     *
     * Example usage:
     *        Log::warning(Log::classBaseName(__METHOD__) . " Example of log usage saving the class name and the method");
     * Generated log:
     *        '12:59:26 WARNING   [app] MyService::myMethod Example of log usage saving the class name and the method'
     */
    public static function classBaseName(string $methodWithCompletePath): string
    {
        $pos = strrpos($methodWithCompletePath, '\\');

        return $pos === false
            ? $methodWithCompletePath
            : substr($methodWithCompletePath, $pos + 1);

    }

}
