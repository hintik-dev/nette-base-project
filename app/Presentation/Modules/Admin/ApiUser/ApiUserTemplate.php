<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin\ApiUser;

use App\Domain\ApiUser\ApiUser;
use App\Model\Latte\BaseTemplate;

final class ApiUserTemplate extends BaseTemplate
{
    public ?ApiUser $apiUser = null;
}
