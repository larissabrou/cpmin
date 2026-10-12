<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

class PortalDateFilter
{
    public static function parse(?string $value, bool $endOfDay = false): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        $value = trim($value);

        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);

                return $endOfDay ? $date->endOfDay() : $date->startOfDay();
            } catch (InvalidFormatException) {
                continue;
            }
        }

        try {
            $date = Carbon::parse($value);

            return $endOfDay ? $date->endOfDay() : $date->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /** Affichage formulaire (JJ/MM/AAAA). */
    public static function formatInput(?string $value): string
    {
        $parsed = self::parse($value);

        return $parsed?->format('d/m/Y') ?? '';
    }

    /** Normalise pour l'API gateway (YYYY-MM-DD). */
    public static function normalizeForApi(?string $value): ?string
    {
        $parsed = self::parse($value);

        return $parsed?->format('Y-m-d');
    }
}
