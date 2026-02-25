<?php declare(strict_types=1);
namespace App\Presentation\Modules\Admin;

use App\Presentation\Modules\Base\BasePresenter;
use Override;
use ReflectionClass;
use ReflectionMethod;

class BaseAdminPresenter extends BasePresenter
{
    /**
     * @param ReflectionMethod|ReflectionClass $element
     * @return void
     * @phpstan-ignore-next-line
     */
    #[Override]
    public function checkRequirements(ReflectionMethod|ReflectionClass $element): void
    {
        if (!$this->user->isLoggedIn()) {
            $this->redirect(
                destination: ':Admin:Sign:in',
                args: ['key' => $this->storeRequest()]
            );
        }

        parent::checkRequirements($element);
    }
}
