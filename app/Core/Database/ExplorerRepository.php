<?php

namespace App\Core\Database;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\InvalidStateException;

class ExplorerRepository
{
    protected Explorer $database;

    protected string $tableName;

    protected function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }

    public function injectBaseDependencies(Explorer $explorer): void
    {
        $this->database = $explorer;
    }


    protected function find(int $id): ?ActiveRow
    {
        return $this->getTable()
            ->get($id);
    }


    protected function findAll(): Selection
    {
        return $this->getTable();
    }


    /**
     * @param array<string, mixed> $criteria
     * @return ActiveRow|null
     */
    protected function findOneBy(array $criteria): ?ActiveRow
    {
        return $this->getTable()
            ->where($criteria)
            ->fetch();
    }


    /**
     * @param array<string, mixed> $criteria
     * @param array<string, mixed>|null $orderBy
     * @param int<0, max>|null $limit
     * @param int<0, max>|null $offset
     * @return Selection
     */
    protected function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): Selection
    {
        $selection = $this->findAll()
            ->where($criteria);

        foreach ($orderBy ?? [] as $column => $order)
        {
            $selection->order($column . ' ' . $order);
        }

        $selection->limit($limit, $offset);

        return $selection;
    }


    protected function getTable(?string $tableName = null): Selection
    {
        if ($tableName === null)
        {
            if (!isset($this->tableName))
            {
                throw new InvalidStateException('Table name is not set.');
            }

            $tableName = $this->tableName;
        }

        return $this->database->table($tableName);
    }
}