<?php declare(strict_types=1);

namespace App\Domain\PasswordReset;

use DateTimeImmutable;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

class ExplorerPasswordResetTokenMapper
{
    public function mapPasswordResetToken(ActiveRow $row): PasswordResetToken
    {
        return new PasswordResetToken(
            id: $row[ExplorerPasswordResetTokenRepository::COLUMN_ID],
            userId: $row[ExplorerPasswordResetTokenRepository::COLUMN_USER_ID],
            tokenHash: $row[ExplorerPasswordResetTokenRepository::COLUMN_TOKEN_HASH],
            expiresAt: DateTimeImmutable::createFromMutable(
                DateTime::from($row[ExplorerPasswordResetTokenRepository::COLUMN_EXPIRES_AT]),
            ),
            usedAt: $row[ExplorerPasswordResetTokenRepository::COLUMN_USED_AT] !== null
                ? DateTimeImmutable::createFromMutable(DateTime::from($row[ExplorerPasswordResetTokenRepository::COLUMN_USED_AT]))
                : null,
            createdAt: DateTimeImmutable::createFromMutable(
                DateTime::from($row[ExplorerPasswordResetTokenRepository::COLUMN_CREATED_AT]),
            ),
        );
    }
}
