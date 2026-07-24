<?php declare(strict_types=1);

namespace App\Model\Utils;

use DateTimeImmutable;
use DateTimeZone;

class DateTimeFactory
{
    public const string TIMEZONE = 'Europe/Prague';

    public static function fromTimestamp(int|float $timestamp): DateTimeImmutable
    {
        return (new DateTimeImmutable('@' . (int) $timestamp))
            ->setTimezone(new DateTimeZone(self::TIMEZONE));
    }

    public static function fromMillisTimestamp(int|float $millis): DateTimeImmutable
    {
        return self::fromTimestamp($millis / 1000);
    }
}
