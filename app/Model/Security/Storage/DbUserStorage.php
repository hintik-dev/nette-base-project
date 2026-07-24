<?php declare(strict_types=1);
namespace App\Model\Security\Storage;

use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserService;
use App\Domain\UserSession\ExplorerUserSessionRepository;
use App\Domain\UserSession\LogoutReason;
use App\Model\Security\IdentityFactory;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Http\Session;
use Nette\Security\IIdentity;
use Nette\Security\User;
use Nette\Security\UserStorage;

/**
 * Session storage backed by user_session DB table.
 * Auth state is stored in a dedicated HTTP-only cookie (not in the PHP session),
 * so PHP session GC cannot log the user out unexpectedly.
 *
 * Cookie behaviour:
 *  - remember = true  (expireDelta = null)  → 1-year cookie, no inactivity timeout
 *  - remember = false (expireDelta set)     → session cookie, inactivity timeout per expireDelta seconds
 */
final class DbUserStorage implements UserStorage
{
    private const string COOKIE_NAME = 'AUTH_TOKEN';

    /** Current inactivity timeout in seconds; null = no timeout. */
    private ?int $expireDeltaSeconds = null;


    public function __construct(
        private readonly Session $session,
        private readonly IRequest $request,
        private readonly IResponse $response,
        private readonly ExplorerUserSessionRepository $sessionRepository,
        private readonly UserService $userService,
        private readonly IdentityFactory $identityFactory,
    ) {
    }


    /**
     * Called once from DI setup — sets the project-wide default inactivity timeout.
     * Can be overridden per-login by setExpiration().
     */
    public function setInactivityTimeout(?string $timeout): void
    {
        if ($timeout === null) {
            $this->expireDeltaSeconds = null;
            return;
        }

        $delta = (int) \Nette\Utils\DateTime::from($timeout)->format('U') - time();
        $this->expireDeltaSeconds = $delta > 0 ? $delta : null;
    }


    public function saveAuthentication(IIdentity $identity): void
    {
        $token = bin2hex(random_bytes(32));

        $dbSession = $this->sessionRepository->create(
            userId:      (int) $identity->getId(),
            ipAddress:   $this->request->getRemoteAddress(),
            userAgent:   $this->request->getHeader('User-Agent'),
            token:       $token,
            expireDelta: $this->expireDeltaSeconds,
        );

        // remember = true (no expireDelta) → long-lived cookie (1 year)
        // remember = false (expireDelta set) → persistent cookie matching server-side inactivity window
        $cookieExpire = $dbSession->expireDelta === null ? '+1 year' : time() + $dbSession->expireDelta;

        $this->response->setCookie(
            self::COOKIE_NAME,
            $token,
            $cookieExpire,
            httpOnly: true,
        );

        $this->session->regenerateId();
    }


    public function clearAuthentication(bool $clearIdentity): void
    {
        $token = $this->getToken();

        if ($token !== null) {
            $dbSession = $this->sessionRepository->findByToken($token);
            if ($dbSession !== null && $dbSession->isActive()) {
                $this->sessionRepository->markAsLoggedOut($dbSession->id, LogoutReason::Manual);
            }

            $this->response->deleteCookie(self::COOKIE_NAME);
        }

        $this->session->regenerateId();
    }


    /**
     * @return array{bool, ?IIdentity, ?int}
     */
    public function getState(): array
    {
        $token = $this->getToken();

        if ($token === null) {
            return [false, null, null];
        }

        $dbSession = $this->sessionRepository->findByToken($token);

        if ($dbSession === null) {
            $this->response->deleteCookie(self::COOKIE_NAME);
            return [false, null, null];
        }

        if (!$dbSession->isActive()) {
            $this->response->deleteCookie(self::COOKIE_NAME);
            $reason = $dbSession->logoutReason === LogoutReason::Inactivity
                ? User::LogoutInactivity
                : User::LogoutManual;
            return [false, null, $reason];
        }

        if ($dbSession->expireDelta !== null) {
            $idleSeconds = time() - $dbSession->lastActivityAt->getTimestamp();
            if ($idleSeconds > $dbSession->expireDelta) {
                $this->sessionRepository->markAsLoggedOut($dbSession->id, LogoutReason::Inactivity);
                $this->response->deleteCookie(self::COOKIE_NAME);
                return [false, null, User::LogoutInactivity];
            }
        }

        $this->sessionRepository->updateLastActivity($dbSession->id);

        try {
            $user = $this->userService->getUserById($dbSession->userId);
        } catch (UserNotFoundException) {
            $this->sessionRepository->markAsLoggedOut($dbSession->id, LogoutReason::Forced);
            $this->response->deleteCookie(self::COOKIE_NAME);
            return [false, null, User::LogoutManual];
        }

        return [true, $this->identityFactory->createIdentity($user), null];
    }


    /**
     * Called from User::setExpiration() — overrides the config default for the current login.
     * Passing null removes any inactivity timeout.
     */
    public function setExpiration(?string $time, bool $clearIdentity = false): void
    {
        if ($time === null) {
            $this->expireDeltaSeconds = null;
        } else {
            $delta = (int) \Nette\Utils\DateTime::from($time)->format('U') - time();
            $this->expireDeltaSeconds = $delta > 0 ? $delta : null;
        }

        // If already authenticated, update the DB record and re-issue the cookie immediately.
        $token = $this->getToken();
        if ($token !== null) {
            $dbSession = $this->sessionRepository->findByToken($token);
            if ($dbSession !== null && $dbSession->isActive()) {
                $this->sessionRepository->updateExpireDelta($dbSession->id, $this->expireDeltaSeconds);

                $cookieExpire = $this->expireDeltaSeconds === null ? '+1 year' : time() + $this->expireDeltaSeconds;
                $this->response->setCookie(self::COOKIE_NAME, $token, $cookieExpire, httpOnly: true);
            }
        }
    }


    private function getToken(): ?string
    {
        $value = $this->request->getCookie(self::COOKIE_NAME);
        return is_string($value) && preg_match('/^[0-9a-f]{64}$/', $value) ? $value : null;
    }
}
