<?php declare(strict_types=1);
namespace App\Domain\ApiUser;

use DateTime;
use Nette\Database\Table\Selection;

class ApiUserFacade
{
    public function __construct(
        private readonly ExplorerApiUserRepository $apiUserRepository,
    ) {
    }


    /** @return Selection<\Nette\Database\Table\ActiveRow> */
    public function getAllSelection(): Selection
    {
        return $this->apiUserRepository->getTableSelection();
    }


    /**
     * @throws ApiUserNotFoundException
     */
    public function getById(int $id): ApiUser
    {
        return $this->apiUserRepository->getById($id);
    }


    public function create(ApiUserFormData $data): int
    {
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
        $token = $this->generateToken();
        $this->apiUserRepository->updateToken($id, $token);
    }


    public function delete(int $id): void
    {
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
}
