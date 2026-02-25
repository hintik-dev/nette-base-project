<?php declare(strict_types=1);
namespace App\Model\Utils;

class DateTimeFormat
{
    public const string DATETIME_FORMAT = "j. n. Y H:i";
    public const string DATETIME_SECONDS_FORMAT = 'j. n. Y H:i:s';
    public const string DATE_FORMAT = "j. n. Y";
    public const string DATE_SHORT_FORMAT = "j. n.";
    public const string TIME_FORMAT = "H:i";
    public const string TIME_SECONDS_FORMAT = "H:i:s";

    public const string DATE_PICKER_FORMAT = 'Y-m-d';
    public const string DATE_PICKER_MAX_VALUE = '9999-12-31';
    public const string DATETIME_PICKER_FORMAT = 'Y-m-d\TH:i';
    public const string DATETIME_SECONDS_PICKER_FORMAT = 'Y-m-d\TH:i:s';
    public const string DATETIME_MILLISECONDS_PICKER_FORMAT = 'Y-m-d\TH:i:s.v';

    public const string DATETIME_PICKER_MAX_VALUE = "9999-12-31T23:59";
}
