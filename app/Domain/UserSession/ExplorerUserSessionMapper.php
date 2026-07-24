<?php declare(strict_types=1);
namespace App\Domain\UserSession;

use DateTimeImmutable;
use Nette\Database\Table\ActiveRow;

class ExplorerUserSessionMapper
{
    public function mapUserSession(ActiveRow $row): UserSession
    {
        return new UserSession(
            id:             $row[ExplorerUserSessionRepository::COLUMN_ID],
            userId:         $row[ExplorerUserSessionRepository::COLUMN_USER_ID],
            ipAddress:      $row[ExplorerUserSessionRepository::COLUMN_IP_ADDRESS],
            userAgent:      $row[ExplorerUserSessionRepository::COLUMN_USER_AGENT],
            token:          $row[ExplorerUserSessionRepository::COLUMN_TOKEN],
            expireDelta:    $row[ExplorerUserSessionRepository::COLUMN_EXPIRE_DELTA] !== null
                                ? (int) $row[ExplorerUserSessionRepository::COLUMN_EXPIRE_DELTA]
                                : null,
            createdAt:      DateTimeImmutable::createFromMutable($row[ExplorerUserSessionRepository::COLUMN_CREATED_AT]),
            lastActivityAt: DateTimeImmutable::createFromMutable($row[ExplorerUserSessionRepository::COLUMN_LAST_ACTIVITY_AT]),
            loggedOutAt:    $row[ExplorerUserSessionRepository::COLUMN_LOGGED_OUT_AT] !== null
                                ? DateTimeImmutable::createFromMutable($row[ExplorerUserSessionRepository::COLUMN_LOGGED_OUT_AT])
                                : null,
            logoutReason:   $row[ExplorerUserSessionRepository::COLUMN_LOGOUT_REASON] !== null
                                ? LogoutReason::from($row[ExplorerUserSessionRepository::COLUMN_LOGOUT_REASON])
                                : null,
        );
    }
}
