<?php declare(strict_types=1);
namespace App\Domain\UserSettings;

enum AppearanceTheme: string
{
    case Light = 'light';
    case Dark = 'dark';
    case System = 'system';


    public function getLabel(): string
    {
        return match($this) {
            self::Light  => 'Světlý',
            self::Dark   => 'Tmavý',
            self::System => 'Dle systému',
        };
    }
}
