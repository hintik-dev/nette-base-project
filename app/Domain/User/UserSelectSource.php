<?php declare(strict_types=1);
namespace App\Domain\User;

use App\Model\Security\Permission\PermissionDefinition;
use App\Presentation\Control\Form\DynamicSelect\DynamicSelectSource;

/**
 * Zdroj pro DynamicSelect/DynamicMultiSelect nad uživateli — zaregistrovaný
 * pod klíčem 'users' v DynamicSelectSourceRegistry (config/services.neon).
 */
class UserSelectSource implements DynamicSelectSource
{
    public function __construct(
        private readonly UserService $userService,
    ) {
    }


    public function getKey(): string
    {
        return 'users';
    }


    public function getPermission(): ?PermissionDefinition
    {
        return UserPermission::ListAll;
    }


    /**
     * @param int<0, max> $limit
     * @return list<array{value: string, label: string}>
     */
    public function search(string $query, int $limit): array
    {
        return array_map(
            static fn(User $user): array => ['value' => (string) $user->id, 'label' => $user->email],
            $this->userService->searchActiveUsers($query, $limit),
        );
    }


    public function resolveLabels(array $values): array
    {
        $ids = array_map(static fn(string $value): int => (int) $value, $values);

        $labels = [];
        foreach ($this->userService->getUsersByIds($ids) as $user) {
            $labels[(string) $user->id] = $user->email;
        }

        return $labels;
    }
}
