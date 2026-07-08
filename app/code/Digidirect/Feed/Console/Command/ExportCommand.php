<?php

namespace Digidirect\Feed\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Digidirect\Feed\Model\FeedFactory;
use Digidirect\Feed\Model\Feed\Exporter;
use Digidirect\Feed\Model\FeedRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends AbstractCommand
{
    const INPUT_FEED_ID = 'id';

    /**
     * @var FeedFactory
     */
    protected $feedFactory;

    /**
     * @var Exporter
     */
    protected $exporter;

    /**
     * @var FeedRepository
     */
    protected $feedRepository;

    /**
     * ExportCommand constructor.
     * @param State $appState
     * @param FeedFactory $feedFactory
     * @param Exporter $exporter
     * @param FeedRepository $feedRepository
     */
    public function __construct(
        State $appState,
        FeedFactory $feedFactory,
        Exporter $exporter,
        FeedRepository $feedRepository
    ) {
        $this->feedFactory = $feedFactory;
        $this->exporter = $exporter;
        $this->feedRepository = $feedRepository;

        parent::__construct($appState);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_FEED_ID,
                null,
                InputOption::VALUE_OPTIONAL,
                'Feed ID',
                false
            )
        ];
        $this->setName('digidirect:feed:export')
            ->setDescription('Export Feed')
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(Area::AREA_FRONTEND);

        $feedId = $input->getOption(self::INPUT_FEED_ID);
        $verbose = $output->getVerbosity() >= 2 ? true : false;

        if ($feedId) {
            $feedsIds = [$feedId];
        } else {
            $feedsIds = $this->feedFactory->create()->getCollection()->getAllIds();
        }

        foreach ($feedsIds as $feedId) {
            /** @var \Digidirect\Feed\Model\Feed $feed */
            try {
                $feed = $this->feedRepository->getById($feedId);

                if ($verbose) {
                    $output->writeln('<info>' . $feed->getName() . '</info>');
                }

                foreach ($this->exporter->exportCli($feed) as $status => $state) {
                    if ($verbose) {
                        $output->writeln('<info>' . ucfirst($status) . '</info>');
                        $output->writeln('<comment>' . $state . '</comment>');
                    }
                }

                $output->writeln('<info>Feed successfully generated.</info>');
                $output->writeln('<info>' . $feed->getUrl() . '</info>');
            } catch (\Exception $e) {
                $output->writeln('<error>Error during feed generation.</error>');
                $output->writeln('<error>' . $e->getMessage() . '</error>');
                return;
            }
        }
    }
}
