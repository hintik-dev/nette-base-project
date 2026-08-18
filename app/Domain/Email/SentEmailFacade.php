<?php declare(strict_types=1);

namespace App\Domain\Email;

use App\Domain\Email\Mail\RawMail;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use App\Model\Security\Authorizator\InsufficientPrivilegesException;
use App\Model\Security\Permission\PermissionDefinition;
use App\Model\Security\SecurityUser;

class SentEmailFacade
{
    public function __construct(
        private readonly ExplorerSentEmailRepository $repository,
        private readonly EmailSenderService $emailSenderService,
        private readonly SecurityUser $securityUser,
    ) {
    }


    /** @return Selection<ActiveRow> */
    public function getAllSelection(): Selection
    {
        $this->assertAllowed(EmailPermission::ListAll);

        return $this->repository->getAllSelection();
    }


    public function getById(int $id): SentEmail
    {
        $this->assertAllowed(EmailPermission::Detail);

        return $this->repository->getById($id);
    }


    public function resend(int $id): void
    {
        $this->assertAllowed(EmailPermission::Resend);

        $email = $this->repository->getById($id);
        $this->emailSenderService->send(
            $email->recipient,
            new RawMail($email->subject, $email->bodyHtml ?? ''),
        );
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
