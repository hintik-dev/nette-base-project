<?php declare(strict_types=1);

namespace App\Command;

use App\Domain\User\UserService;
use App\Domain\UserRole\UserRole;
use App\Model\Security\Passwords;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'app:create-admin', description: 'Vytvoří nového admin uživatele nebo aktualizuje heslo existujícího')]
class CreateAdminCommand extends BaseCommand
{
    public function __construct(
        private readonly UserService $userService,
        private readonly Passwords $passwords,
    ) {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'E-mail administrátora')
            ->addOption('update', 'u', InputOption::VALUE_NONE, 'Aktualizovat heslo, pokud uživatel již existuje');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $allowUpdate = $input->getOption('update');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $output->writeln('<error>Neplatná e-mailová adresa: ' . $email . '</error>');
            return self::FAILURE;
        }

        $userExists = $this->userService->userExistsByEmail($email);

        if ($userExists && !$allowUpdate) {
            $output->writeln('<error>Uživatel s e-mailem "' . $email . '" již existuje.</error>');
            $output->writeln('<comment>Použijte volbu --update (-u) pro aktualizaci hesla.</comment>');
            return self::FAILURE;
        }

        $password = $this->askForPassword($input, $output);

        if ($password === null) {
            return self::FAILURE;
        }

        $passwordHash = $this->passwords->hash($password);

        if ($userExists) {
            $user = $this->userService->getUserByEmail($email);
            $this->userService->updateUserPasswordHash($user->id, $passwordHash);
            $output->writeln('<info>Heslo uživatele "' . $email . '" bylo aktualizováno.</info>');
        } else {
            $user = $this->userService->createUser($email, $passwordHash, UserRole::ADMIN);
            $output->writeln('<info>Admin uživatel "' . $email . '" byl úspěšně vytvořen (ID: ' . $user->id . ').</info>');
        }

        return self::SUCCESS;
    }


    private function askForPassword(InputInterface $input, OutputInterface $output): ?string
    {
        $helper = $this->getHelper('question');

        assert($helper instanceof QuestionHelper);

        $question = new Question('Zadejte heslo: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $question->setValidator(function (?string $value): string {
            if ($value === null || trim($value) === '') {
                throw new \RuntimeException('Heslo nesmí být prázdné.');
            }
            if (strlen($value) < 8) {
                throw new \RuntimeException('Heslo musí mít alespoň 8 znaků.');
            }
            return $value;
        });

        $password = $helper->ask($input, $output, $question);

        $confirmQuestion = new Question('Potvrďte heslo: ');
        $confirmQuestion->setHidden(true);
        $confirmQuestion->setHiddenFallback(false);

        $confirm = $helper->ask($input, $output, $confirmQuestion);

        if ($password !== $confirm) {
            $output->writeln('<error>Hesla se neshodují.</error>');
            return null;
        }

        return $password;
    }
}
