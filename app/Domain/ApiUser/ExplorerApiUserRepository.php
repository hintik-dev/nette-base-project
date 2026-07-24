<?php declare(strict_types=1);
namespace App\Domain\ApiUser;

use App\Core\Database\ExplorerRepository;
use DateTime;
use Nette\Database\Table\Selection;

class ExplorerApiUserRepository extends ExplorerRepository
{
    public const string COLUMN_ID = 'id';
    public const string COLUMN_NAME = 'name';
    public const string COLUMN_TOKEN = 'token';
    public const string COLUMN_DESCRIPTION = 'description';
    public const string COLUMN_IS_ACTIVE = 'is_active';
    public const string COLUMN_VALID_FROM = 'valid_from';
    public const string COLUMN_VALID_TO = 'valid_to';
    public const string COLUMN_CREATED_AT = 'created_at';
    public const string COLUMN_DELETED_AT = 'deleted_at';

    public const string TABLE_NAME = 'api_user';


    public function __construct(
        private readonly ExplorerApiUserMapper $apiUserMapper,
    ) {
        parent::__construct(self::TABLE_NAME);
    }


    /** @return Selection<\Nette\Database\Table\ActiveRow> */
    public function getTableSelection(): Selection
    {
        return $this->getTable()->where(self::COLUMN_DELETED_AT, null);
    }


    /**
     * @throws ApiUserNotFoundException
     */
    public function getById(int $id): ApiUser
    {
        $row = $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->where(self::COLUMN_DELETED_AT, null)
            ->fetch();

        if ($row === null) {
            throw new ApiUserNotFoundException();
        }

        return $this->apiUserMapper->mapApiUser($row);
    }


    /**
     * @throws ApiUserNotFoundException
     */
    public function getByToken(string $token): ApiUser
    {
        $row = $this->getTable()
            ->where(self::COLUMN_TOKEN, $token)
            ->where(self::COLUMN_IS_ACTIVE, true)
            ->where(self::COLUMN_DELETED_AT, null)
            ->where('valid_from IS NULL OR valid_from <= NOW()')
            ->where('valid_to IS NULL OR valid_to >= NOW()')
            ->fetch();

        if ($row === null) {
            throw new ApiUserNotFoundException();
        }

        return $this->apiUserMapper->mapApiUser($row);
    }


    public function create(string $name, ?string $description, bool $isActive, ?DateTime $validFrom, ?DateTime $validTo, string $token): int
    {
        $row = $this->getTable()->insert([
            self::COLUMN_NAME        => $name,
            self::COLUMN_DESCRIPTION => $description,
            self::COLUMN_IS_ACTIVE   => $isActive,
            self::COLUMN_VALID_FROM  => $validFrom,
            self::COLUMN_VALID_TO    => $validTo,
            self::COLUMN_TOKEN       => $token,
        ]);

        assert($row instanceof \Nette\Database\Table\ActiveRow);

        return (int) $row[self::COLUMN_ID];
    }


    public function update(int $id, string $name, ?string $description, bool $isActive, ?DateTime $validFrom, ?DateTime $validTo): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([
                self::COLUMN_NAME        => $name,
                self::COLUMN_DESCRIPTION => $description,
                self::COLUMN_IS_ACTIVE   => $isActive,
                self::COLUMN_VALID_FROM  => $validFrom,
                self::COLUMN_VALID_TO    => $validTo,
            ]);
    }


    public function updateToken(int $id, string $token): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([self::COLUMN_TOKEN => $token]);
    }


    public function delete(int $id): void
    {
        $this->getTable()
            ->where(self::COLUMN_ID, $id)
            ->update([self::COLUMN_DELETED_AT => new DateTime()]);
    }
}
