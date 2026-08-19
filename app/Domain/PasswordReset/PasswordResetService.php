<?php declare(strict_types=1);

namespace App\Domain\PasswordReset;

use App\Domain\Email\Mail\PasswordResetMail;
use App\Domain\Email\MailQueueService;
use App\Domain\Notification\NotificationService;
use App\Domain\Notification\NotificationType;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserService;
use App\Domain\UserSession\ExplorerUserSessionRepository;
use App\Domain\UserSession\LogoutReason;
use App\Model\Security\Passwords;
use DateTimeImmutable;
use Nette\Application\LinkGenerator;

class PasswordResetService
{
    private const string TOKEN_TTL = '+1 hour';

    public function __construct(
        private readonly ExplorerPasswordResetTokenRepository $tokenRepository,
        private readonly UserService $userService,
        private readonly Passwords $passwords,
        private readonly MailQueueService $mailQueueService,
        private readonly LinkGenerator $linkGenerator,
        private readonly ExplorerUserSessionRepository $userSessionRepository,
        private readonly NotificationService $notificationService,
    ) {
    }


    /**
     * Nikdy nevyhazuje výjimku ani jinak neprozrazuje, jestli e-mail v systému existuje.
     */
    public function requestReset(string $email): void
    {
        try {
            $user = $this->userService->getUserByEmail($email);
        } catch (UserNotFoundException) {
            return;
        }

        if (!$user->active) {
            return;
        }

        $this->tokenRepository->invalidateAllForUser($user->id);

        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = (new DateTimeImmutable())->modify(self::TOKEN_TTL);

        $this->tokenRepository->create($user->id, $tokenHash, $expiresAt);

        // Vedoucí "//" vynutí absolutní URL — odkaz jde mimo HTTP request (do e-mailu).
        $resetUrl = $this->linkGenerator->link('//Admin:Sign:resetPassword', ['token' => $rawToken]);

        $this->mailQueueService->enqueue($user->email, new PasswordResetMail($resetUrl));
    }


    /**
     * @throws PasswordResetTokenInvalidException
     */
    public function validateToken(string $rawToken): PasswordResetToken
    {
        $tokenHash = hash('sha256', $rawToken);
        $token = $this->tokenRepository->findValidByTokenHash($tokenHash);

        if ($token === null) {
            throw new PasswordResetTokenInvalidException();
        }

        return $token;
    }


    /**
     * @throws PasswordResetTokenInvalidException
     */
    public function resetPassword(string $rawToken, string $newPassword): void
    {
        $token = $this->validateToken($rawToken);

        $this->userService->updateUserPasswordHash($token->userId, $this->passwords->hash($newPassword));
        $this->tokenRepository->markUsed($token->id);
        $this->userSessionRepository->markAllActiveAsLoggedOutForUser($token->userId, LogoutReason::PasswordReset);

        $this->notificationService->notifyUser(
            $token->userId,
            NotificationType::Security,
            'Heslo bylo změněno',
            'Vaše heslo bylo právě změněno. Pokud jste to nebyli vy, kontaktujte administrátora.',
        );
    }
}
