<?php declare(strict_types=1);

namespace App\Presentation\Components\Admin\AppSettings\AppSettingsGeneralForm;

interface AppSettingsGeneralFormFactory
{
    public function create(): AppSettingsGeneralForm;
}
