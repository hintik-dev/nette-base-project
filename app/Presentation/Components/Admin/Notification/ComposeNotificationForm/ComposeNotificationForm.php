<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\Notification\ComposeNotificationForm;

use App\Domain\Notification\NotificationService;
use App\Domain\Notification\NotificationType;
use App\Domain\User\UserService;
use App\Domain\UserRole\UserRoleService;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;
use stdClass;

class ComposeNotificationForm extends BaseComponent
{
    private const string TARGET_ALL = 'all';
    private const string TARGET_ROLES = 'roles';
    private const string TARGET_USERS = 'users';

    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly UserService $userService,
        private readonly UserRoleService $userRoleService,
    ) {
    }


    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();

        $target = $form->addRadioList('target', 'Komu', [
            self::TARGET_ALL   => 'Všem aktivním uživatelům',
            self::TARGET_ROLES => 'Vybraným rolím',
            self::TARGET_USERS => 'Konkrétním uživatelům',
        ])->setDefaultValue(self::TARGET_ALL)->setRequired();

        $roleIds = $form->addMultiSelect('roleIds', 'Role', $this->getRoleOptions());
        $target->addCondition($form::Equal, self::TARGET_ROLES)->toggle('notification-target-roles');
        $roleIds->addConditionOn($target, $form::Equal, self::TARGET_ROLES)
            ->addRule($form::Filled, 'Vyberte alespoň jednu roli.');

        $userIds = $form->addMultiSelect('userIds', 'Uživatelé', $this->getUserOptions());
        $target->addCondition($form::Equal, self::TARGET_USERS)->toggle('notification-target-users');
        $userIds->addConditionOn($target, $form::Equal, self::TARGET_USERS)
            ->addRule($form::Filled, 'Vyberte alespoň jednoho uživatele.');

        $form->addSelect('type', 'Typ', $this->getTypeOptions())
            ->setRequired()
            ->setDefaultValue(NotificationType::System->value);

        $form->addText('title', 'Nadpis')
            ->setRequired('Zadejte nadpis.');

        $form->addTextArea('message', 'Zpráva')
            ->setHtmlAttribute('rows', 6)
            ->setRequired('Zadejte text zprávy.');

        $form->addSubmit('submit', 'Odeslat notifikaci');

        $form->onSuccess[] = fn(BaseForm $form, stdClass $values) => $this->saveForm($values);

        return $form;
    }


    private function saveForm(stdClass $values): void
    {
        $type = NotificationType::from($values->type);

        match ($values->target) {
            self::TARGET_ROLES => $this->notificationService->notifyByRoles(
                array_values(array_map('intval', $values->roleIds)),
                $type,
                $values->title,
                $values->message,
            ),
            self::TARGET_USERS => $this->notificationService->notifyUsers(
                array_values(array_map('intval', $values->userIds)),
                $type,
                $values->title,
                $values->message,
            ),
            default => $this->notificationService->broadcastToActive($type, $values->title, $values->message),
        };

        $this->flashSuccess('Notifikace byla odeslána.');
        $this->presenter->redirect(':Admin:Notification:all');
    }


    /** @return array<string, string> */
    private function getTypeOptions(): array
    {
        return [
            NotificationType::System->value   => 'Systém',
            NotificationType::Info->value     => 'Info',
            NotificationType::Success->value  => 'Úspěch',
            NotificationType::Warning->value  => 'Upozornění',
            NotificationType::Security->value => 'Zabezpečení',
        ];
    }


    /** @return array<int, string> */
    private function getRoleOptions(): array
    {
        $options = [];

        foreach ($this->userRoleService->getAssignableRoles() as $role) {
            $options[$role->id] = $role->name;
        }

        return $options;
    }


    /** @return array<int, string> */
    private function getUserOptions(): array
    {
        $options = [];

        foreach ($this->userService->getActiveUsers() as $user) {
            $options[$user->id] = $user->email;
        }

        return $options;
    }
}
