<?php declare(strict_types=1);

namespace App\Domain\Notification;

use App\Model\Security\SecurityUser;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * Čtecí strana — vždy scoped na aktuálního přihlášeného uživatele.
 * "Moje notifikace" nejsou spravovaná cizí entita, žádné ACL navíc —
 * gate je běžné AdminPermission::Access z BaseAdminPresenter.
 */
class NotificationFacade
{
    public function __construct(
        private readonly ExplorerNotificationRecipientRepository $repository,
        private readonly SecurityUser $securityUser,
    ) {
    }


    public function countUnread(): int
    {
        return $this->repository->countUnreadForUser($this->currentUserId());
    }


    /** @return UserNotification[] */
    public function getRecent(int $limit): array
    {
        return $this->repository->findRecentForUser($this->currentUserId(), $limit);
    }


    /** @return Selection<ActiveRow> */
    public function getAllSelection(): Selection
    {
        return $this->repository->getAllSelectionForUser($this->currentUserId());
    }


    public function markAsRead(int $recipientId): void
    {
        $this->repository->markAsRead($recipientId, $this->currentUserId());
    }


    public function markAllAsRead(): void
    {
        $this->repository->markAllAsReadForUser($this->currentUserId());
    }


    public function hide(int $recipientId): void
    {
        $this->repository->hide($recipientId, $this->currentUserId());
    }


    private function currentUserId(): int
    {
        return (int) $this->securityUser->getId();
    }
}
