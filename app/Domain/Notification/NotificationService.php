<?php declare(strict_types=1);

namespace App\Domain\Notification;

use App\Domain\User\ExplorerUserRepository;
use DateTimeImmutable;

/**
 * Veřejné API pro vytváření notifikací — volá se přímo z ostatních domén
 * (žádný event bus v projektu není), stejná role jako MailQueueService.
 */
class NotificationService
{
    public function __construct(
        private readonly ExplorerNotificationRepository $notificationRepository,
        private readonly ExplorerNotificationRecipientRepository $recipientRepository,
        private readonly ExplorerUserRepository $userRepository,
    ) {
    }


    /** @param ?string $link Už vyřešená URL (např. přes LinkGenerator/$presenter->link()), ne presenter destination string. */
    public function notifyUser(int $userId, NotificationType $type, string $title, string $message, ?string $link = null): void
    {
        $this->notifyUsers([$userId], $type, $title, $message, $link);
    }


    /**
     * @param int[] $userIds
     * @param ?string $link Už vyřešená URL, ne presenter destination string.
     */
    public function notifyUsers(array $userIds, NotificationType $type, string $title, string $message, ?string $link = null): void
    {
        if ($userIds === []) {
            return;
        }

        $notificationId = $this->notificationRepository->create($type, $title, $message, $link);
        $this->recipientRepository->createMany($notificationId, $userIds);
    }


    public function broadcastToActive(NotificationType $type, string $title, string $message, ?string $link = null): void
    {
        $this->notifyUsers($this->userRepository->getActiveUserIds(), $type, $title, $message, $link);
    }


    /** Volá výhradně PruneNotificationsJob. */
    public function pruneOld(DateTimeImmutable $before): int
    {
        return $this->recipientRepository->deleteOlderThan($before);
    }
}
