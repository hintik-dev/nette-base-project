<?php declare(strict_types=1);

namespace App\Domain\Email;

use App\Core\Database\ExplorerRepository;
use DateTimeImmutable;
use Nette\Database\Table\Selection;

class ExplorerSentEmailRepository extends ExplorerRepository
{
    public const string TABLE_NAME = 'sent_email';

    public const string COLUMN_ID = 'id';
    public const string COLUMN_RECIPIENT = 'recipient';
    public const string COLUMN_SUBJECT = 'subject';
    public const string COLUMN_BODY_HTML = 'body_html';
    public const string COLUMN_STATUS = 'status';
    public const string COLUMN_ERROR = 'error';
    public const string COLUMN_SENT_AT = 'sent_at';
    public const string COLUMN_CREATED_AT = 'created_at';


    public function __construct(
        private readonly ExplorerSentEmailMapper $mapper,
    ) {
        parent::__construct(self::TABLE_NAME);
    }


    public function getById(int $id): SentEmail
    {
        $row = $this->find($id);

        if ($row === null) {
            throw new SentEmailNotFoundException($id);
        }

        return $this->mapper->mapSentEmail($row);
    }


    /** @return Selection<\Nette\Database\Table\ActiveRow> */
    public function getAllSelection(): Selection
    {
        return $this->findAll()->order(self::COLUMN_CREATED_AT . ' DESC');
    }


    public function insert(string $recipient, string $subject, ?string $bodyHtml): int
    {
        $row = $this->getTable()->insert([
            self::COLUMN_RECIPIENT  => $recipient,
            self::COLUMN_SUBJECT    => $subject,
            self::COLUMN_BODY_HTML  => $bodyHtml,
            self::COLUMN_STATUS     => SentEmailStatus::Pending->value,
            self::COLUMN_CREATED_AT => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        assert($row instanceof \Nette\Database\Table\ActiveRow);

        return (int) $row[self::COLUMN_ID];
    }


    public function markSent(int $id): void
    {
        $this->getTable()
            ->get($id)
            ?->update([
                self::COLUMN_STATUS  => SentEmailStatus::Sent->value,
                self::COLUMN_SENT_AT => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
    }


    public function markFailed(int $id, string $error): void
    {
        $this->getTable()
            ->get($id)
            ?->update([
                self::COLUMN_STATUS => SentEmailStatus::Failed->value,
                self::COLUMN_ERROR  => $error,
            ]);
    }
}
