<?php declare(strict_types=1);

namespace App\Presentation\Modules\Admin\DynamicSelect;

use App\Presentation\Control\Form\DynamicSelect\DynamicSelectSourceRegistry;
use App\Presentation\Modules\Admin\BaseAdminPresenter;
use Nette\Http\IResponse;

/**
 * Jediný sdílený AJAX endpoint pro všechny DynamicSelect/DynamicMultiSelect
 * v remote režimu — konkrétní zdroj dat vybírá parametr source
 * (viz DynamicSelectSourceRegistry a config/services.neon).
 */
class DynamicSelectPresenter extends BaseAdminPresenter
{
    private const int LIMIT = 20;

    public function __construct(
        private readonly DynamicSelectSourceRegistry $sourceRegistry,
    ) {
        parent::__construct();
    }


    public function actionSearch(string $source, string $q = ''): void
    {
        $selectSource = $this->sourceRegistry->find($source);
        if ($selectSource === null) {
            $this->error('Unknown dynamic select source.');
        }

        $permission = $selectSource->getPermission();
        if ($permission !== null && !$this->getSecurityUser()->isAllowed($permission)) {
            $this->error('Insufficient privileges', IResponse::S403_Forbidden);
        }

        $this->sendJson(['items' => $selectSource->search(trim($q), self::LIMIT)]);
    }
}
