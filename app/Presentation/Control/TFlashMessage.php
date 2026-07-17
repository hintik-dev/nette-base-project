<?php declare(strict_types=1);

namespace App\Presentation\Control;

use App\Model\Utils\FlashMessage;
use App\Presentation\Modules\Base\BasePresenter;
use stdClass;

/**
 * @mixin BasePresenter
 */
trait TFlashMessage
{
    /**
     * @param string $message
     * @param string $type
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @internal
     */
    public function flashMessage($message, $type = 'info'): stdClass
    {
        $class = parent::flashMessage($message, $type);

        if ($this->isAjax())
        {
            $this->redrawControl('flashes');
        }

        return $class;
    }

    public function flashInfo(string $message): stdClass
    {
        return $this->flashMessage($message, 'info');
    }

    public function flashSuccess(string $message): stdClass
    {
        return $this->flashMessage($message, 'success');
    }

    public function flashWarning(string $message): stdClass
    {
        return $this->flashMessage($message, 'warning');
    }

    public function flashError(string $message): stdClass
    {
        return $this->flashMessage($message, 'error');
    }

    public function flash(FlashMessage $flashMessage): stdClass
    {
        return match ($flashMessage->getType())
        {
            FlashMessage::TYPE_SUCCESS => $this->flashSuccess($flashMessage->getMessage()),
            FlashMessage::TYPE_INFO    => $this->flashInfo($flashMessage->getMessage()),
            FlashMessage::TYPE_WARNING => $this->flashWarning($flashMessage->getMessage()),
            FlashMessage::TYPE_ERROR   => $this->flashError($flashMessage->getMessage()),
            default                    => $this->flashMessage($flashMessage->getMessage()),
        };
    }
}
