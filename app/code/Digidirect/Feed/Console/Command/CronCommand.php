<?php

namespace Digidirect\Feed\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Digidirect\Feed\Cron\Export as CronExport;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommand extends AbstractCommand
{
    /**
     * @var CronExport
     */
    protected $cronExport;

    /**
     * CronCommand constructor.
     * @param State $appState
     * @param CronExport $cronExport
     */
    public function __construct(
        State $appState,
        CronExport $cronExport
    ) {
        $this->cronExport = $cronExport;

        parent::__construct($appState);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('digidirect:feed:cron')
            ->setDescription('Run cron jobs for extension')
            ->setDefinition([]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(Area::AREA_FRONTEND);

        $this->cronExport->execute();
    }
}
