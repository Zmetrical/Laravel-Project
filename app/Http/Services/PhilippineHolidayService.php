<?php

namespace App\Http\Services;
    
use Carbon\Carbon;

class PhilippineHolidayService
{
    /**
     * Returns all Philippine holidays for the given year.
     *
     * Key   : 'YYYY-MM-DD'
     * Value : ['name' => string, 'type' => 'regular'|'special']
     *
     * @return array<string, array{name: string, type: string}>
     */
    public function forYear(int $year): array
    {
        $holidays = [];

        // ── Fixed Regular Holidays ───────────────────────────────────────────
        $fixed = [
            '01-01' => "New Year's Day",
            '04-09' => 'Araw ng Kagitingan',
            '05-01' => 'Labor Day',
            '06-12' => 'Independence Day',
            '08-26' => 'National Heroes Day',   // Last Monday of August (approx)
            '11-30' => 'Bonifacio Day',
            '12-25' => 'Christmas Day',
            '12-30' => 'Rizal Day',
        ];

        foreach ($fixed as $mmdd => $name) {
            $holidays["{$year}-{$mmdd}"] = ['name' => $name, 'type' => 'regular'];
        }

        // ── Fixed Special Non-Working Holidays ───────────────────────────────
        $special = [
            '11-01' => "All Saints' Day",
            '11-02' => "All Souls' Day",
            '12-08' => 'Immaculate Conception Day',
            '12-24' => 'Christmas Eve',
            '12-31' => "New Year's Eve",
        ];

        foreach ($special as $mmdd => $name) {
            $holidays["{$year}-{$mmdd}"] = ['name' => $name, 'type' => 'special'];
        }

        // ── Movable Regular Holidays (Holy Week) ─────────────────────────────
        // Easter Sunday is the reference point; Maundy Thursday = -3, Good Friday = -2
        $easter         = Carbon::createFromTimestamp(easter_date($year));
        $maundyThursday = $easter->copy()->subDays(3);
        $goodFriday     = $easter->copy()->subDays(2);
        $blackSaturday  = $easter->copy()->subDays(1);

        $holidays[$maundyThursday->toDateString()] = ['name' => 'Maundy Thursday',  'type' => 'regular'];
        $holidays[$goodFriday->toDateString()]      = ['name' => 'Good Friday',      'type' => 'regular'];
        $holidays[$blackSaturday->toDateString()]   = ['name' => 'Black Saturday',   'type' => 'special'];

        // ── National Heroes Day — last Monday of August ───────────────────────
        $heroesDay = Carbon::create($year, 8, 1)->lastOfMonth(Carbon::MONDAY);
        $holidays[$heroesDay->toDateString()] = ['name' => 'National Heroes Day', 'type' => 'regular'];
        // Remove the fixed approximation if we added it
        unset($holidays["{$year}-08-26"]);

        ksort($holidays);

        return $holidays;
    }

    /**
     * Returns the holiday for a specific date, or null if none.
     *
     * @return array{name: string, type: string}|null
     */
    public function forDate(Carbon|string $date): ?array
    {
        $dateStr = $date instanceof Carbon
            ? $date->toDateString()
            : $date;

        $year = (int) substr($dateStr, 0, 4);

        return $this->forYear($year)[$dateStr] ?? null;
    }
}