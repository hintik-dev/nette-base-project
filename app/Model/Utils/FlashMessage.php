<?php declare(strict_types=1);
namespace App\Model\Utils;

class FlashMessage
{
    public const TYPE_SUCCESS = 'success';
    public const TYPE_WARNING = 'warning';
    public const TYPE_INFO = 'info';
    public const TYPE_ERROR = 'error';

    private string $message;
    private string $type;

    public function __construct(string $message, string $type)
    {
        $this->message = $message;
        $this->type = $type;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
