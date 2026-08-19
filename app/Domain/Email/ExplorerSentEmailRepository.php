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
    public const string COLUMN_MAIL_CLASS = 'mail_class';
    public const string COLUMN_IS_SENSITIVE = 'is_sensitive';
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


    /**
     * Vloží záznam s již vyřešeným výsledkem (Sent/Failed) — volá worker po zpracování fronty.
     * Pro is_sensitive e-maily se $bodyHtml nikdy neukládá (volající předá null).
     */
    public function insertResolved(
        string $recipient,
        string $subject,
        ?string $bodyHtml,
        SentEmailStatus $status,
        ?string $error,
        ?string $mailClass,
        bool $isSensitive,
        ?DateTimeImmutable $sentAt,
    ): int {
        $row = $this->getTable()->insert([
            self::COLUMN_RECIPIENT    => $recipient,
            self::COLUMN_SUBJECT      => $subject,
            self::COLUMN_BODY_HTML    => $bodyHtml,
            self::COLUMN_MAIL_CLASS   => $mailClass,
            self::COLUMN_IS_SENSITIVE => $isSensitive,
            self::COLUMN_STATUS       => $status->value,
            self::COLUMN_ERROR        => $error,
            self::COLUMN_SENT_AT      => $sentAt?->format('Y-m-d H:i:s'),
            self::COLUMN_CREATED_AT   => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        assert($row instanceof \Nette\Database\Table\ActiveRow);

        return (int) $row[self::COLUMN_ID];
    }
}
