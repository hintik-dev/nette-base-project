<?php declare(strict_types=1);

namespace App\Presentation\Modules\Admin\ScheduledJob;

use App\Domain\ScheduledJob\ScheduledJobFacade;
use App\Presentation\Components\Admin\ScheduledJob\ScheduledJobGrid\ScheduledJobGridFactory;
use App\Presentation\Components\Admin\ScheduledJob\ScheduledJobRunGrid\ScheduledJobRunGridFactory;
use App\Presentation\Components\Admin\ScheduledJob\ScheduledJobRunOutputGrid\ScheduledJobRunOutputGridFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;
use App\Domain\ScheduledJob\ScheduledJobPermission;
use App\Presentation\Accessory\RequiresPermission;

#[RequiresPermission(ScheduledJobPermission::ListAll)]
class ScheduledJobPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly ScheduledJobGridFactory $gridFactory,
        private readonly ScheduledJobRunGridFactory $runGridFactory,
        private readonly ScheduledJobRunOutputGridFactory $outputGridFactory,
        private readonly ScheduledJobFacade $facade,
    ) {
        parent::__construct();
    }


    public function actionDefault(): void
    {
        $this->addComponent($this->gridFactory->create(), 'scheduledJobGrid');
    }


    #[RequiresPermission(ScheduledJobPermission::RunHistory)]
    public function actionRuns(?int $jobId = null): void
    {
        /** @var ScheduledJobRunsTemplate $template */
        $template = $this->template;
        $template->job = $jobId !== null ? $this->facade->getById($jobId) : null;
        $this->addComponent($this->runGridFactory->create($jobId), 'scheduledJobRunGrid');
    }


    #[RequiresPermission(ScheduledJobPermission::RunHistory)]
    public function actionRunDetail(int $id): void
    {
        $run = $this->facade->getRunById($id);

        /** @var ScheduledJobRunDetailTemplate $template */
        $template = $this->template;
        $template->run = $run;
        $template->job = $this->facade->getById($run->scheduledJobId);
        $this->addComponent($this->outputGridFactory->create($id), 'scheduledJobRunOutputGrid');
    }


    #[RequiresPermission(ScheduledJobPermission::Edit)]
    public function handleSetActive(int $id, bool $active): void
    {
        $this->facade->setActive($id, $active);
        $this->flashSuccess('Stav úlohy byl změněn.');
        $this->redirect('this');
    }


    #[RequiresPermission(ScheduledJobPermission::RunNow)]
    public function handleRunNow(int $id): void
    {
        try {
            $this->facade->runNow($id);
            $this->flashSuccess('Úloha byla naplánována ke spuštění.');
        } catch (\Throwable) {
            $this->flashError('Nepodařilo se naplánovat úlohu.');
        }

        $this->redirect('this');
    }
}
