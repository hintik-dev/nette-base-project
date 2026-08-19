<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Notification\NotificationsBell;

use App\Domain\Notification\NotificationFacade;
use App\Presentation\Components\Base\BaseComponent;

class NotificationsBell extends BaseComponent
{
    private const int RECENT_LIMIT = 8;

    public function __construct(
        private readonly NotificationFacade $facade,
    ) {
    }


    public function render(mixed $params = null): void
    {
        $this->template->unreadCount = $this->facade->countUnread();
        $this->template->recentNotifications = $this->facade->getRecent(self::RECENT_LIMIT);

        parent::render($params);
    }


    public function handleMarkAsRead(int $id): void
    {
        $link = $this->facade->markAsRead($id);

        if ($link !== null) {
            $this->presenter->redirectUrl($link);
        }

        $this->redrawOrRedirect();
    }


    public function handleMarkAllAsRead(): void
    {
        $this->facade->markAllAsRead();
        $this->redrawOrRedirect();
    }


    public function handleHide(int $id): void
    {
        $this->facade->hide($id);
        $this->redrawOrRedirect();
    }


    private function redrawOrRedirect(): void
    {
        if ($this->presenter->isAjax()) {
            $this->redrawControl('bell');
        } else {
            $this->presenter->redirect('this');
        }
    }
}
