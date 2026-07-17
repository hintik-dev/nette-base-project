<?php declare(strict_types=1);

namespace App\Domain\ScheduledJob;

use App\Core\Database\ExplorerRepository;
use Nette\Database\Table\Selection;

class ExplorerScheduledJobRepository extends ExplorerRepository
{
    public const string COLUMN_ID = 'id';
    public const string COLUMN_NAME = 'name';
    public const string COLUMN_CLASS = 'class';
    public const string COLUMN_DESCRIPTION = 'description';
    public const string COLUMN_CRON = 'cron';
    public const string COLUMN_IS_ACTIVE = 'is_active';
    public const string COLUMN_CREATED_AT = 'created_at';

    public const string TABLE_NAME = 'scheduled_job';


    public function __construct(
        private readonly ExplorerScheduledJobMapper $mapper,
    ) {
        parent::__construct(self::TABLE_NAME);
    }


    public function getById(int $id): ScheduledJob
    {
        $row = $this->find($id);

        if ($row === null) {
            throw new ScheduledJobNotFoundException($id);
        }

        return $this->mapper->mapScheduledJob($row);
    }


    /** @return ScheduledJob[] */
    public function findAllMapped(): array
    {
        return array_map(
            fn($row) => $this->mapper->mapScheduledJob($row),
            iterator_to_array(parent::findAll()),
        );
    }


    /** @return ScheduledJob[] */
    public function findAllActive(): array
    {
        return array_map(
            fn($row) => $this->mapper->mapScheduledJob($row),
            iterator_to_array(
                $this->getTable()->where(self::COLUMN_IS_ACTIVE, true),
            ),
        );
    }


    public function findByClass(string $class): ?ScheduledJob
    {
        $row = $this->getTable()
            ->where(self::COLUMN_CLASS, $class)
            ->fetch();

        return $row !== null ? $this->mapper->mapScheduledJob($row) : null;
    }


    /** @return Selection<\Nette\Database\Table\ActiveRow> */
    public function getAllSelection(): Selection
    {
        return parent::findAll();
    }


    public function toggleActive(int $id): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([
                self::COLUMN_IS_ACTIVE => new \Nette\Database\SqlLiteral('NOT ' . self::COLUMN_IS_ACTIVE),
            ]);
    }
}
