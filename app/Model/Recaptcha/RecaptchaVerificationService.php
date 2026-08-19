<?php declare(strict_types=1);
namespace App\Model\Recaptcha;

use ReCaptcha\ReCaptcha;

class RecaptchaVerificationService
{
    public function __construct(
        private readonly ReCaptcha $reCaptcha,
        private readonly string $siteKey,
    ) {
    }


    public function getSiteKey(): string
    {
        return $this->siteKey;
    }


    public function verify(string $token, ?string $remoteIp, string $expectedHostname): bool
    {
        if ($token === '') {
            return false;
        }

        return $this->reCaptcha
            ->setExpectedHostname($expectedHostname)
            ->verify($token, $remoteIp)
            ->isSuccess();
    }
}
