<?php declare(strict_types=1);

namespace App\Domain\Notification;

use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\SecurityUser;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * Čtecí strana. Metody bez assertAllowed() jsou vždy scoped na aktuálního
 * přihlášeného uživatele ("moje notifikace") — nejsou to spravovaná cizí
 * entita, gate je běžné AdminPermission::Access z BaseAdminPresenter.
 * getSystemSelection() je výjimka — přehled napříč všemi uživateli, gatovaný
 * NotificationPermission::ListAll.
 */
class NotificationFacade
{
    public function __construct(
        private readonly ExplorerNotificationRecipientRepository $repository,
        private readonly ExplorerNotificationRepository $notificationRepository,
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


    /**
     * Všechny notifikace v systému, napříč uživateli — jen pro oprávněné.
     * @return Selection<ActiveRow>
     */
    public function getSystemSelection(): Selection
    {
        $this->assertAllowed(NotificationPermission::ListAll);

        return $this->notificationRepository->getAllSelection();
    }


    public function markAsRead(int $recipientId): ?string
    {
        return $this->repository->markAsRead($recipientId, $this->currentUserId());
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


    /**
     * @throws InsufficientPrivilegesException
     */
    private function assertAllowed(PermissionDefinition $permission): void
    {
        if (!$this->securityUser->isAllowed($permission)) {
            throw new InsufficientPrivilegesException();
        }
    }
}
