<?php declare(strict_types=1);
namespace App\Model\Latte;

use App\Model\Security\SecurityUser;
use App\Presentation\Control\BaseControl;
use App\Presentation\Modules\Base\BasePresenter;
use Nette\Bridges\ApplicationLatte\Template;

/**
 * @property-read SecurityUser $_user
 * @property-read BasePresenter $presenter
 * @property-read BaseControl $control
 * @property-read string $baseUri
 * @property-read string $basePath
 * @property-read array<array{message: string, type: string}> $flashes
 */
final class TemplateProperty extends Template
{
}
