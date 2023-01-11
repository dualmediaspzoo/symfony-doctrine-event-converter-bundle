<?php

namespace DualMedia\DoctrineEventConverterBundle\Exception;

/**
 * Basic shared class for exceptions in the bundle
 *
 * Catch this, if you're unsure what you might encounter at some point
 */
abstract class AbstractEventDistributorException extends \Exception
{
    protected const MESSAGE_TEMPLATE = 'Unknown exception occurred';

    /**
     * Returns a new exception with a preformatted message
     *
     * @param list<mixed> $arguments
     *
     * @return static
     */
    public static function new(
        array $arguments = []
    ): static {
        return new static(static::formatMessage($arguments)); // @phpstan-ignore-line
    }

    /**
     * Get the exception message
     *
     * @param list<mixed> $arguments
     *
     * @return string
     */
    public static function formatMessage(
        array $arguments = []
    ): string {
        return sprintf(static::MESSAGE_TEMPLATE, ...$arguments); // @phpstan-ignore-line
    }
}
