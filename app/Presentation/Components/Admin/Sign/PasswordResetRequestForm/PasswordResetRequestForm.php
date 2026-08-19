<?php declare(strict_types=1);
namespace App\Presentation\Components\Admin\Sign\PasswordResetRequestForm;

use App\Domain\PasswordReset\PasswordResetFacade;
use App\Domain\PasswordReset\PasswordResetRequestFormData;
use App\Model\Recaptcha\RecaptchaVerificationService;
use App\Presentation\Components\Base\BaseComponent;
use App\Presentation\Control\Form\BaseForm;
use Nette\Http\IRequest;

class PasswordResetRequestForm extends BaseComponent
{
    public function __construct(
        private readonly PasswordResetFacade $facade,
        private readonly RecaptchaVerificationService $recaptchaVerificationService,
        private readonly IRequest $httpRequest,
    ) {
    }


    public function createComponentForm(): BaseForm
    {
        $form = new BaseForm();

        $form->addEmail(PasswordResetRequestFormData::PARAM_EMAIL, 'E-mail')
            ->setRequired('Zadejte e-mail.');

        $form->addSubmit('submit', 'Odeslat odkaz pro obnovu hesla');

        $form->onSuccess[] = fn(BaseForm $form, PasswordResetRequestFormData $data) => $this->saveForm($form, $data);

        return $form;
    }


    public function render(mixed $params = null): void
    {
        $this->template->recaptchaSiteKey = $this->recaptchaVerificationService->getSiteKey();
        parent::render($params);
    }


    private function saveForm(BaseForm $form, PasswordResetRequestFormData $data): void
    {
        // g-recaptcha-response obsahuje pomlčku, nejde tedy zavést jako běžný pojmenovaný form control.
        $token = (string) $this->httpRequest->getPost('g-recaptcha-response');
        $hostname = (string) $this->httpRequest->getUrl()->getHost();

        if (!$this->recaptchaVerificationService->verify($token, $this->httpRequest->getRemoteAddress(), $hostname)) {
            $form->addError('Ověření reCAPTCHA se nezdařilo. Zkuste to prosím znovu.');
            return;
        }

        $this->facade->requestReset($data->email);

        // Vždy stejná zpráva bez ohledu na to, jestli e-mail v systému existuje (ochrana proti enumeraci účtů).
        $this->flashSuccess('Pokud účet s tímto e-mailem existuje, poslali jsme na něj odkaz pro obnovu hesla.');
        $this->presenter->redirect(':Admin:Sign:in');
    }
}
