<?php declare(strict_types=1);

namespace App\Domain\Email;

use App\Core\Database\ExplorerRepository;
use App\Domain\Email\Mail\Mail;
use DateTimeImmutable;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class ExplorerMailQueueRepository extends ExplorerRepository
{
    public const string TABLE_NAME = 'mail_queue';

    public const string COLUMN_ID = 'id';
    public const string COLUMN_RECIPIENT = 'recipient';
    public const string COLUMN_SUBJECT = 'subject';
    public const string COLUMN_BODY_HTML = 'body_html';
    public const string COLUMN_MAIL_CLASS = 'mail_class';
    public const string COLUMN_IS_SENSITIVE = 'is_sensitive';
    public const string COLUMN_PRIORITY = 'priority';
    public const string COLUMN_STATUS = 'status';
    public const string COLUMN_ATTEMPTS = 'attempts';
    public const string COLUMN_MAX_ATTEMPTS = 'max_attempts';
    public const string COLUMN_NEXT_ATTEMPT_AT = 'next_attempt_at';
    public const string COLUMN_LOCKED_AT = 'locked_at';
    public const string COLUMN_LOCKED_BY = 'locked_by';
    public const string COLUMN_ERROR = 'error';
    public const string COLUMN_CREATED_AT = 'created_at';


    public function __construct(
        private readonly ExplorerMailQueueMapper $mapper,
    ) {
        parent::__construct(self::TABLE_NAME);
    }


    /** @return Selection<ActiveRow> */
    public function getAllSelection(): Selection
    {
        return $this->findAll()->order(self::COLUMN_PRIORITY . ' DESC, ' . self::COLUMN_ID . ' ASC');
    }


    public function enqueue(string $recipient, Mail $mail, int $priority = 0): int
    {
        $row = $this->getTable()->insert([
            self::COLUMN_RECIPIENT    => $recipient,
            self::COLUMN_SUBJECT      => $mail->getSubject(),
            self::COLUMN_BODY_HTML    => $mail->getBodyHtml(),
            self::COLUMN_MAIL_CLASS   => $mail::class,
            self::COLUMN_IS_SENSITIVE => $mail->isSensitive(),
            self::COLUMN_PRIORITY     => $priority,
            self::COLUMN_STATUS       => MailQueueStatus::Queued->value,
            self::COLUMN_CREATED_AT   => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        assert($row instanceof ActiveRow);

        return (int) $row[self::COLUMN_ID];
    }


    /** @return MailQueue[] */
    public function claimBatch(int $limit, string $workerId): array
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        $ids = array_keys($this->getTable()
            ->where(self::COLUMN_STATUS, MailQueueStatus::Queued->value)
            ->where('next_attempt_at IS NULL OR next_attempt_at <= ?', $now)
            ->order(self::COLUMN_PRIORITY . ' DESC, ' . self::COLUMN_ID . ' ASC')
            ->limit(max(0, $limit))
            ->fetchPairs(self::COLUMN_ID));

        if ($ids === []) {
            return [];
        }

        $this->getTable()
            ->where(self::COLUMN_ID, $ids)
            ->where(self::COLUMN_STATUS, MailQueueStatus::Queued->value)
            ->update([
                self::COLUMN_STATUS    => MailQueueStatus::Processing->value,
                self::COLUMN_LOCKED_AT => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                self::COLUMN_LOCKED_BY => $workerId,
            ]);

        $rows = $this->getTable()
            ->where(self::COLUMN_LOCKED_BY, $workerId)
            ->where(self::COLUMN_STATUS, MailQueueStatus::Processing->value)
            ->fetchAll();

        return array_values(array_map(
            fn(ActiveRow $row): MailQueue => $this->mapper->mapMailQueue($row),
            $rows,
        ));
    }


    public function retryLater(int $id, string $error, DateTimeImmutable $nextAttemptAt, int $attempts): void
    {
        $this->getTable()
            ->get($id)
            ?->update([
                self::COLUMN_STATUS           => MailQueueStatus::Queued->value,
                self::COLUMN_ATTEMPTS         => $attempts,
                self::COLUMN_ERROR            => $error,
                self::COLUMN_NEXT_ATTEMPT_AT  => $nextAttemptAt->format('Y-m-d H:i:s'),
                self::COLUMN_LOCKED_AT        => null,
                self::COLUMN_LOCKED_BY        => null,
            ]);
    }


    public function delete(int $id): void
    {
        $this->getTable()->get($id)?->delete();
    }
}
