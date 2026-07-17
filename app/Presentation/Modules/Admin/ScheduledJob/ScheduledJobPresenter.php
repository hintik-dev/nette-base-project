<?php declare(strict_types=1);

namespace App\Presentation\Modules\Admin\ScheduledJob;

use App\Domain\ScheduledJob\ScheduledJobFacade;
use App\Presentation\Components\Admin\ScheduledJob\ScheduledJobGrid\ScheduledJobGridFactory;
use App\Presentation\Components\Admin\ScheduledJob\ScheduledJobRunGrid\ScheduledJobRunGridFactory;
use App\Presentation\Components\Admin\ScheduledJob\ScheduledJobRunOutputGrid\ScheduledJobRunOutputGridFactory;
use App\Presentation\Modules\Admin\BaseAdminPresenter;

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


    public function actionRuns(?int $jobId = null): void
    {
        $this->template->job = $jobId !== null ? $this->facade->getById($jobId) : null;
        $this->addComponent($this->runGridFactory->create($jobId), 'scheduledJobRunGrid');
    }


    public function actionRunDetail(int $id): void
    {
        $run = $this->facade->getRunById($id);
        $this->template->run = $run;
        $this->template->job = $this->facade->getById($run->scheduledJobId);
        $this->addComponent($this->outputGridFactory->create($id), 'scheduledJobRunOutputGrid');
    }


    public function handleToggle(int $id): void
    {
        $this->facade->toggleActive($id);
        $this->flashSuccess('Stav úlohy byl změněn.');
        $this->redirect('this');
    }


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
