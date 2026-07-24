<?php declare(strict_types=1);
namespace App\Domain\ApiUser;

class ApiUserFormData
{
    public const string PARAM_NAME = 'name';
    public const string PARAM_DESCRIPTION = 'description';
    public const string PARAM_IS_ACTIVE = 'isActive';
    public const string PARAM_VALID_FROM = 'validFrom';
    public const string PARAM_VALID_TO = 'validTo';

    public string $name;
    public ?string $description;
    public bool $isActive;
    public ?string $validFrom;
    public ?string $validTo;
}
