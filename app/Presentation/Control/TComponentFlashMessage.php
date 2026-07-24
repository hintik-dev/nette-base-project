<?php declare(strict_types=1);

namespace App\Presentation\Control;

use App\Model\Utils\FlashMessage;

trait TComponentFlashMessage
{
    /** @var callable[] */
    public array $onFlash;

    public function flashSuccess(string $message): void
    {
        $this->onFlash(new FlashMessage($message, FlashMessage::TYPE_SUCCESS));
    }

    public function flashWarning(string $message): void
    {
        $this->onFlash(new FlashMessage($message, FlashMessage::TYPE_WARNING));
    }

    public function flashInfo(string $message): void
    {
        $this->onFlash(new FlashMessage($message, FlashMessage::TYPE_INFO));
    }

    public function flashError(string $message): void
    {
        $this->onFlash(new FlashMessage($message, FlashMessage::TYPE_ERROR));
    }
}
