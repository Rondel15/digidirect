<?php
namespace Digidirect\AI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AIListCommand extends Command
{
    /**
     * @var \Digidirect\AI\Model\ResourceModel\Integrations\Integrations\Grid\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Class constructor
     *
     * @param \Digidirect\AI\Model\ResourceModel\Integrations\Integrations\Grid\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Digidirect\AI\Model\ResourceModel\Integrations\Integrations\Grid\CollectionFactory $collectionFactory
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('ai:process:list');
        $this->setDescription('List Of Existing Processes');
        parent::configure();
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return void
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        unset($input);
        $collection = $this->_collectionFactory->create()->getItems();
        $counter = 0;
        foreach ($collection as $integration) {
            $format = $output->getFormatter();
            $string = $format->format(
                (++$counter) . '. Integration Name: '
                . $integration['integration_name']
                . ', Process Code: '
                . $integration['process_code']
            );
            $output->writeln(
                $string
            );
        }
    }
}
