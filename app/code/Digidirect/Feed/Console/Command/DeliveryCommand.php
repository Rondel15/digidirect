<?php

namespace Digidirect\Feed\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Digidirect\Feed\Model\FeedRepository;
use Digidirect\Feed\Model\Feed\Deliverer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeliveryCommand extends AbstractCommand
{
    const INPUT_FEED_ID = 'id';

    /**
     * @var FeedRepository
     */
    protected $feedRepository;

    /**
     * @var Deliverer
     */
    protected $deliverer;

    /**
     * DeliveryCommand constructor.
     * @param State $appState
     * @param FeedRepository $feedRepository
     * @param Deliverer $deliverer
     */
    public function __construct(
        State $appState,
        FeedRepository $feedRepository,
        Deliverer $deliverer
    ) {
        $this->feedRepository = $feedRepository;
        $this->deliverer = $deliverer;

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
                InputOption::VALUE_REQUIRED,
                'Feed ID',
                false
            )
        ];
        $this->setName('digidirect:feed:delivery')
            ->setDescription('Delivery Feed')
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
        $verbose = $output->getVerbosity() == 2 ? true : false;

        if (!intval($feedId)) {
            $output->writeln('<error>Invalid feed id for option "id".</error>');
            return;
        }

        try {
            $feed = $this->feedRepository->getById($feedId);

            if (!$feed->getId()) {
                $output->writeln('<error>Feed not exists.</error>');
                return;
            }

            if ($verbose) {
                $output->writeln('<info>' . $feed->getName() . '</info>');
            }

            $this->deliverer->delivery($feed);

            $output->writeln('<info>' . __('Feed was successfully delivered to "%1"', $feed->getFtpHost()) . '</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . __('Unable to delivery feed. %1', $e->getMessage()) . '</error>');
        }
    }
}
