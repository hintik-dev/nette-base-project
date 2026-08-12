<?php declare(strict_types=1);
namespace App\Domain\ApiUser;

use DateTime;
use Nette\Database\Table\Selection;
use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\SecurityUser;

class ApiUserFacade
{
    public function __construct(
        private readonly ExplorerApiUserRepository $apiUserRepository,
        private readonly SecurityUser $securityUser,
    ) {
    }


    /** @return Selection<\Nette\Database\Table\ActiveRow> */
    public function getAllSelection(): Selection
    {
        $this->assertAllowed(ApiUserPermission::ListAll);

        return $this->apiUserRepository->getTableSelection();
    }


    /**
     * @throws ApiUserNotFoundException
     */
    public function getById(int $id): ApiUser
    {
        $this->assertAllowed(ApiUserPermission::ListAll);

        return $this->apiUserRepository->getById($id);
    }


    public function create(ApiUserFormData $data): int
    {
        $this->assertAllowed(ApiUserPermission::Create);

        $token = $this->generateToken();
        return $this->apiUserRepository->create(
            $data->name,
            $data->description,
            $data->isActive,
            $this->parseDateTime($data->validFrom),
            $this->parseDateTime($data->validTo),
            $token,
        );
    }


    public function update(int $id, ApiUserFormData $data): void
    {
        $this->assertAllowed(ApiUserPermission::Edit);

        $this->apiUserRepository->update(
            $id,
            $data->name,
            $data->description,
            $data->isActive,
            $this->parseDateTime($data->validFrom),
            $this->parseDateTime($data->validTo),
        );
    }


    public function regenerateToken(int $id): void
    {
        $this->assertAllowed(ApiUserPermission::RegenerateToken);

        $token = $this->generateToken();
        $this->apiUserRepository->updateToken($id, $token);
    }


    public function delete(int $id): void
    {
        $this->assertAllowed(ApiUserPermission::Delete);

        $this->apiUserRepository->delete($id);
    }


    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }


    private function parseDateTime(?string $value): ?DateTime
    {
        if ($value === null || $value === '') {
            return null;
        }
        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $value);
        return $dt !== false ? $dt : null;
    }


    /**
     * @throws InsufficientPrivilegesException
     */
    private function assertAllowed(PermissionDefinition $permission): void
    {
        if (!$this->securityUser->isAllowed($permission)) {
            throw new InsufficientPrivilegesException();
        }
    }
}
