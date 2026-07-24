<?php declare(strict_types=1);

namespace App\Domain\Email;

use App\Domain\Email\Mail\RawMail;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class SentEmailFacade
{
    public function __construct(
        private readonly ExplorerSentEmailRepository $repository,
        private readonly EmailSenderService $emailSenderService,
    ) {
    }


    /** @return Selection<ActiveRow> */
    public function getAllSelection(): Selection
    {
        return $this->repository->getAllSelection();
    }


    public function getById(int $id): SentEmail
    {
        return $this->repository->getById($id);
    }


    public function resend(int $id): void
    {
        $email = $this->repository->getById($id);
        $this->emailSenderService->send(
            $email->recipient,
            new RawMail($email->subject, $email->bodyHtml ?? ''),
        );
    }
}
