<?php declare(strict_types=1);

namespace App\Presentation\Control\DataGrid\Column;

class ColumnDateTime extends \Contributte\Datagrid\Column\ColumnDateTime
{
    private const int DEFAULT_MIN_WIDTH_PX = 195;

    public function __construct(
        \Contributte\Datagrid\Datagrid $grid,
        string $key,
        string $column,
        string $name,
    ) {
        parent::__construct($grid, $key, $column, $name);
        $this->setMinWidth(self::DEFAULT_MIN_WIDTH_PX);
    }

    public function setMinWidth(?int $widthPx): static
    {
        if ($widthPx === null) {
            $this->getElementPrototype('td')
                ->setAttribute(
                    name: 'style',
                    value: $this->removeMinWidthFromStyle(
                        style: (string) ($this->getElementPrototype('td')->getAttribute('style') ?? '')
                    )
                );
            $this->getElementPrototype('th')
                ->setAttribute(
                    name: 'style',
                    value: $this->removeMinWidthFromStyle(
                        style: (string) ($this->getElementPrototype('th')->getAttribute('style') ?? '')
                    )
                );
            return $this;
        }

        $style = $this->buildStyleWithMinWidth($widthPx);

        $this->getElementPrototype('td')->setAttribute('style', $style);
        $this->getElementPrototype('th')->setAttribute('style', $style);

        return $this;
    }

    private function buildStyleWithMinWidth(int $widthPx): string
    {
        $style = $this->removeMinWidthFromStyle((string) ($this->getElementPrototype('td')->getAttribute('style') ?? ''));

        if ($style !== '' && !str_ends_with($style, ';')) {
            $style .= ';';
        }

        $style .= 'min-width:' . $widthPx . 'px;';

        return $style;
    }

    private function removeMinWidthFromStyle(string $style): string
    {
        return trim(preg_replace('~\s*min-width\s*:[^;]+;?~i', '', $style) ?? '');
    }
}
