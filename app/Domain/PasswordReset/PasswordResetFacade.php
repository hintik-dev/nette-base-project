<?php declare(strict_types=1);

namespace App\Domain\PasswordReset;

/**
 * Veřejné API pro flow "zapomenuté heslo" — čistě anonymní, žádné ACL.
 * Přirozené místo, kam se v další fázi zapojí ověření reCAPTCHA (requestReset()).
 */
class PasswordResetFacade
{
    public function __construct(
        private readonly PasswordResetService $service,
    ) {
    }


    public function requestReset(string $email): void
    {
        $this->service->requestReset($email);
    }


    public function isTokenValid(string $rawToken): bool
    {
        try {
            $this->service->validateToken($rawToken);
            return true;
        } catch (PasswordResetTokenInvalidException) {
            return false;
        }
    }


    /**
     * @throws PasswordResetTokenInvalidException
     */
    public function resetPassword(string $rawToken, string $newPassword): void
    {
        $this->service->resetPassword($rawToken, $newPassword);
    }
}
