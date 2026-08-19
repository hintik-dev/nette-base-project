<?php declare(strict_types=1);

namespace App\Domain\Notification;

use DateTimeImmutable;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

class ExplorerNotificationRecipientMapper
{
    /** Mapuje řádek notification_recipient joinovaný se sloupci notification (viz ExplorerNotificationRecipientRepository::JOINED_COLUMNS). */
    public function mapUserNotification(ActiveRow $row): UserNotification
    {
        return new UserNotification(
            recipientId: $row[ExplorerNotificationRecipientRepository::COLUMN_ID],
            notificationId: $row[ExplorerNotificationRecipientRepository::COLUMN_NOTIFICATION_ID],
            type: NotificationType::from($row[ExplorerNotificationRecipientRepository::COLUMN_TYPE]),
            title: $row[ExplorerNotificationRecipientRepository::COLUMN_TITLE],
            message: $row[ExplorerNotificationRecipientRepository::COLUMN_MESSAGE],
            link: $row[ExplorerNotificationRecipientRepository::COLUMN_LINK],
            notificationCreatedAt: DateTimeImmutable::createFromMutable(
                DateTime::from($row[ExplorerNotificationRecipientRepository::COLUMN_NOTIFICATION_CREATED_AT]),
            ),
            readAt: $row[ExplorerNotificationRecipientRepository::COLUMN_READ_AT] !== null
                ? DateTimeImmutable::createFromMutable(DateTime::from($row[ExplorerNotificationRecipientRepository::COLUMN_READ_AT]))
                : null,
            hiddenAt: $row[ExplorerNotificationRecipientRepository::COLUMN_HIDDEN_AT] !== null
                ? DateTimeImmutable::createFromMutable(DateTime::from($row[ExplorerNotificationRecipientRepository::COLUMN_HIDDEN_AT]))
                : null,
        );
    }
}
