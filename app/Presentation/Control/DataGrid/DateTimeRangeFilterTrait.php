<?php declare(strict_types=1);

namespace App\Presentation\Control\DataGrid;

use App\Model\Utils\DateTimeFactory;
use Contributte\Datagrid\Filter\FilterDateRange;
use DateTimeImmutable;
use DateTimeZone;
use Nette\Utils\ArrayHash;

trait DateTimeRangeFilterTrait
{
    /** @var list<string> */
    private const array DATE_TIME_RANGE_FILTER_FORMATS = [
        'd.m.Y H:i:s',
        'd.m.Y H:i',
        'd.m.Y',
        'Y-m-d\\TH:i:s',
        'Y-m-d\\TH:i',
        'Y-m-d H:i:s',
        'Y-m-d H:i',
        'Y-m-d',
    ];

    public function addFilterDateRange(string $key, string $name, ?string $column = null, string $nameSecond = '-'): FilterDateRange
    {
        $filter = parent::addFilterDateRange($key, $name, $column, $nameSecond);
        $filter->addAttribute('class', 'flatpickr-input');
        $filter->setAttribute('data-date-language', $this->translator?->translate('base.lang.code_short'));
        $filter->setTemplate(__DIR__ . '/templates/datagrid_filter_daterange.latte');
        return $filter;
    }

    public function addFilterDateTimeRange(string $key, string $name, ?string $column = null, string $nameSecond = '-'): FilterDateRange
    {
        $filter = $this->addFilterDateRange($key, $name, $column, $nameSecond);
        $filter->setAttribute('data-date-format', 'd.m.Y H:i');
        $filter->setAttribute('data-enable-time', 'true');
        $filter->setAttribute('data-time_24hr', 'true');
        $filter->setAttribute('data-minute-increment', '1');
        $filter->setAttribute('data-mode', 'single');
        $filter->setAttribute('data-single-range', 'true');
        return $filter;
    }

    public function addFilterDateTimeRangeFromTimestamp(
        string $key,
        string $name,
        ?string $column = null,
        string $nameSecond = '-'
    ): FilterDateRange {
        $column ??= $key;
        $filter = $this->addFilterDateTimeRange($key, $name, $column, $nameSecond);
        $filter->setCondition(function ($selection, mixed $value) use ($column): void {
            $range = $this->normalizeRangeValue($value);
            $from = $this->parseDateTimeRangeBoundary($range['from'] ?? null, false);
            $to = $this->parseDateTimeRangeBoundary($range['to'] ?? null, true);

            if ($from !== null) {
                $selection->where($column . ' >= ?', $from);
            }

            if ($to !== null) {
                $selection->where($column . ' < ?', $to);
            }
        });

        return $filter;
    }

    public function addFilterDateTimeRangeFromMillisTimestamp(
        string $key,
        string $name,
        ?string $column = null,
        string $nameSecond = '-'
    ): FilterDateRange {
        $column ??= $key;
        $filter = $this->addFilterDateTimeRange($key, $name, $column, $nameSecond);
        $filter->setCondition(function ($selection, mixed $value) use ($column): void {
            $range = $this->normalizeRangeValue($value);
            $from = $this->parseDateTimeRangeBoundary($range['from'] ?? null, false);
            $to = $this->parseDateTimeRangeBoundary($range['to'] ?? null, true);

            if ($from !== null) {
                $selection->where($column . ' >= ?', $from * 1000);
            }

            if ($to !== null) {
                $selection->where($column . ' < ?', $to * 1000);
            }
        });

        return $filter;
    }

    public function addFilterDateTimeRangeFromDatetime(
        string $key,
        string $name,
        ?string $column = null,
        string $nameSecond = '-'
    ): FilterDateRange {
        $column ??= $key;
        $filter = $this->addFilterDateTimeRange($key, $name, $column, $nameSecond);
        $filter->setCondition(function ($selection, mixed $value) use ($column): void {
            $range = $this->normalizeRangeValue($value);
            $from = $this->parseDateTimeRangeBoundaryAsDatetime($range['from'] ?? null, false);
            $to = $this->parseDateTimeRangeBoundaryAsDatetime($range['to'] ?? null, true);

            if ($from !== null) {
                $selection->where($column . ' >= ?', $from);
            }

            if ($to !== null) {
                $selection->where($column . ' < ?', $to);
            }
        });

        return $filter;
    }

    /** @return array{from?: mixed, to?: mixed} */
    private function normalizeRangeValue(mixed $value): array
    {
        if ($value instanceof ArrayHash) {
            $range = ['from' => $value['from'] ?? null, 'to' => $value['to'] ?? null];
            return $this->normalizeSingleInputRange($range);
        }

        if (is_array($value)) {
            $range = ['from' => $value['from'] ?? null, 'to' => $value['to'] ?? null];
            return $this->normalizeSingleInputRange($range);
        }

        if (is_object($value)) {
            $range = ['from' => $value->from ?? null, 'to' => $value->to ?? null];
            return $this->normalizeSingleInputRange($range);
        }

        return [];
    }

    /**
     * @param array{from?: mixed, to?: mixed} $range
     * @return array{from?: mixed, to?: mixed}
     */
    private function normalizeSingleInputRange(array $range): array
    {
        if (($range['to'] ?? null) !== null && trim((string) $range['to']) !== '') {
            return $range;
        }

        $from = $range['from'] ?? null;

        if (!is_scalar($from)) {
            return $range;
        }

        $fromString = trim((string) $from);

        if ($fromString === '') {
            return $range;
        }

        foreach ([' do ', ' to '] as $separator) {
            if (!str_contains($fromString, $separator)) {
                continue;
            }

            [$start, $end] = explode($separator, $fromString, 2);
            $range['from'] = trim($start);
            $range['to'] = trim($end);
            return $range;
        }

        return $range;
    }

    private function parseDateTimeRangeBoundaryAsDatetime(mixed $rawValue, bool $upperBound): ?DateTimeImmutable
    {
        $timestamp = $this->parseDateTimeRangeBoundary($rawValue, $upperBound);
        if ($timestamp === null) {
            return null;
        }

        return (new DateTimeImmutable())->setTimestamp($timestamp);
    }

    private function parseDateTimeRangeBoundary(mixed $rawValue, bool $upperBound): ?int
    {
        if ($rawValue === null) {
            return null;
        }

        if ($rawValue instanceof \DateTimeInterface) {
            $dateTime = DateTimeImmutable::createFromInterface($rawValue);
            return $upperBound
                ? $dateTime->modify('+1 second')->getTimestamp()
                : $dateTime->getTimestamp();
        }

        if (!is_scalar($rawValue)) {
            return null;
        }

        $value = trim((string) $rawValue);

        if ($value === '') {
            return null;
        }

        $timezone = new DateTimeZone(DateTimeFactory::TIMEZONE);

        foreach (self::DATE_TIME_RANGE_FILTER_FORMATS as $format) {
            $dateTime = DateTimeImmutable::createFromFormat('!' . $format, $value, $timezone);

            if ($dateTime === false) {
                continue;
            }

            if (!$upperBound) {
                return $dateTime->getTimestamp();
            }

            if (str_contains($format, 'H:i:s')) {
                return $dateTime->modify('+1 second')->getTimestamp();
            }

            if (str_contains($format, 'H:i')) {
                return $dateTime->modify('+1 minute')->getTimestamp();
            }

            return $dateTime->modify('+1 day')->getTimestamp();
        }

        return null;
    }
}
