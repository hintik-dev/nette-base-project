<?php declare(strict_types=1);
namespace App\Presentation\Modules\Base;

use App\Model\Latte\BaseTemplate;
use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\SecurityUser;
use App\Model\Utils\FlashMessage;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\TFlashMessage;
use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Nette\Http\IResponse;
use Override;

/**
 * @property-read BaseTemplate $template
 */
class BasePresenter extends Presenter
{
    use TFlashMessage;

    /**
     * Záchytná síť pro kontroly ve fasádách. Ty se volají i z komponent, takže
     * výjimka může vyletět kdykoli během běhu presenteru — bez tohohle by
     * skončila jako chyba 500 místo 403.
     *
     * Primární cestou zůstává atribut RequiresPermission, který uživatele
     * odkloní dřív, než se k fasádě vůbec dostane.
     */
    #[Override]
    public function run(Request $request): Response
    {
        try {
            return parent::run($request);
        } catch (InsufficientPrivilegesException $e) {
            throw new BadRequestException($e->getMessage(), IResponse::S403_Forbidden, $e);
        }
    }


    /**
     * Nette typuje $this->user jako Nette\Security\User; aplikace ale používá
     * potomka SecurityUser s vlastním isAllowed() a forceLogout().
     */
    protected function getSecurityUser(): SecurityUser
    {
        $user = $this->getUser();
        assert($user instanceof SecurityUser);

        return $user;
    }


    public function addComponent(IComponent $component, ?string $name, ?string $insertBefore = null): static
    {
        if ($component instanceof BaseComponent) {
            $component->onFlash[] = function (FlashMessage $flashMessage): void {
                $this->flash($flashMessage);
            };
        }

        return parent::addComponent($component, $name, $insertBefore);
    }
}
