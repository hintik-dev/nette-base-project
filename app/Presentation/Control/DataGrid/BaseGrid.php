<?php declare(strict_types=1);

namespace App\Presentation\Control\DataGrid;

use App\Model\Utils\DateTimeFactory;
use App\Presentation\Control\DataGrid\Column\ColumnDateTime;
use App\Presentation\Control\DataGrid\Column\ColumnText;
use Contributte\Datagrid\Column\Action;
use Contributte\Datagrid\Column\ActionCallback;
use Contributte\Datagrid\Column\MultiAction;
use Contributte\Datagrid\Components\DatagridPaginator\DatagridPaginator;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Filter\FilterSelect;
use Contributte\Datagrid\Filter\FilterText;
use Nette\Localization\Translator;

class BaseGrid extends Datagrid
{
    use DateTimeRangeFilterTrait;

    public static string $iconPrefix = 'bi bi-';

    public const string TEST_TYPE_ATTR = 'type';

    private ?string $aggregationRowLabel = null;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplateFile(__DIR__ . '/templates/datagrid.latte');
        $this->setRememberState(false);
    }

    public function addColumnText(string $key, string $name, ?string $column = null): ColumnText
    {
        $column ??= $key;
        $columnText = new ColumnText($this, $key, $column, $name);
        $this->addColumn($key, $columnText);
        return $columnText;
    }

    public function addColumnDateTime(string $key, string $name, ?string $column = null): ColumnDateTime
    {
        $column ??= $key;
        $columnDateTime = new ColumnDateTime($this, $key, $column, $name);
        $this->addColumn($key, $columnDateTime);
        return $columnDateTime;
    }

    public function addColumnDateTimeFromTimestamp(
        string $key,
        string $name,
        ?string $column = null,
        bool $withMillis = false,
    ): ColumnDateTime {
        return $this->addColumnDateTime($key, $name, $column)
            ->setRenderer(function ($row) use ($column, $key, $withMillis) {
                $timestamp = $row[$column ?? $key];
                if ($timestamp === null || $timestamp === '' || $timestamp === 0) {
                    return null;
                }

                $timestamp = (float) $timestamp;
                $dateTime = $withMillis
                    ? DateTimeFactory::fromMillisTimestamp($timestamp)
                    : DateTimeFactory::fromTimestamp($timestamp);
                return str_replace(' ', "\u{00A0}", $dateTime->format('j. n. Y')) . ' ' . $dateTime->format('H:i');
            });
    }

    public function setTranslator(Translator $translator): Datagrid
    {
        return parent::setTranslator($translator);
    }

    public function setAggregationRowLabel(?string $label): self
    {
        $this->aggregationRowLabel = $label;
        return $this;
    }

    public function getAggregationRowLabel(): ?string
    {
        return $this->aggregationRowLabel;
    }

    public function createComponentPaginator(): DatagridPaginator
    {
        $paginator = parent::createComponentPaginator();
        $paginator->setTemplateFile(__DIR__ . '/templates/data_grid_paginator.latte');
        return $paginator;
    }

    /** @param array<string> $columns */
    public function addFilterText(string $key, string $name, $columns = null): FilterText
    {
        $filter = parent::addFilterText($key, $name, $columns);
        $filter->setTemplate(__DIR__ . '/templates/datagrid_filter_text.latte');
        $filter->setAttribute('placeholder', $name);
        return $filter;
    }

    /** @param array<string, string> $options */
    public function addFilterSelect(string $key, string $name, array $options, ?string $column = null): FilterSelect
    {
        $filter = parent::addFilterSelect($key, $name, $options, $column);
        $filter->setAttribute('class', ['form-select', 'form-select-sm']);
        return $filter;
    }

    /**
     * @param string $key
     * @param string $name
     * @param string|null $href
     * @param array<mixed>|null $params
     */
    public function addAction(string $key, string $name, ?string $href = null, ?array $params = null): Action
    {
        $action = parent::addAction($key, $name, $href, $params);
        $action->setDataAttribute(self::TEST_TYPE_ATTR, $key);
        return $action;
    }

    public function addActionCallback(string $key, string $name, ?callable $callback = null): ActionCallback
    {
        $actionCallback = parent::addActionCallback($key, $name, $callback);
        $actionCallback->setDataAttribute(self::TEST_TYPE_ATTR, $key);
        return $actionCallback;
    }

    public function addMultiAction(string $key, string $name): MultiAction
    {
        $this->addActionCheck($key);
        $action = new \App\Presentation\Control\DataGrid\Column\MultiAction($this, $key, $name);
        $this->actions[$key] = $action;
        return $action;
    }
}
