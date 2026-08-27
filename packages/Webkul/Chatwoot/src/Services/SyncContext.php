<?php

namespace Webkul\Chatwoot\Services;

class SyncContext
{
    /**
     * Active sync source ('chatwoot', 'krayin', or null).
     */
    protected static ?string $source = null;

    /**
     * Set the current synchronization source.
     */
    public static function setSource(?string $source): void
    {
        static::$source = $source;
    }

    /**
     * Get the current synchronization source.
     */
    public static function getSource(): ?string
    {
        return static::$source;
    }

    /**
     * Check if currently processing an inbound Chatwoot sync.
     */
    public static function isFromChatwoot(): bool
    {
        return static::$source === 'chatwoot';
    }

    /**
     * Check if currently processing an outbound Krayin sync.
     */
    public static function isFromKrayin(): bool
    {
        return static::$source === 'krayin';
    }

    /**
     * Execute a callback without triggering recursive sync loops.
     */
    public static function executeWithoutLoop(callable $callback, string $source = 'chatwoot')
    {
        $previous = static::$source;
        static::$source = $source;

        try {
            return $callback();
        } finally {
            static::$source = $previous;
        }
    }
}
