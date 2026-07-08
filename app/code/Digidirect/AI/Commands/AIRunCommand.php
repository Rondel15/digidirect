<?php

namespace Digidirect\AI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AIRunCommand
 * @package Digidirect\AI\Commands
 */
class AIRunCommand extends Command
{
    /**
     * @var \Digidirect\AI\Model\Engine\EngineFactory
     */
    protected $engineFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * AIRunCommand constructor.
     * @param \Digidirect\AI\Model\Engine\EngineFactory $engineFactory
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Digidirect\AI\Model\Engine\EngineFactory $engineFactory,
        \Magento\Framework\App\State $appState
    ) {
        $this->engineFactory = $engineFactory;
        $this->appState = $appState;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('ai:process:run');
        $this->setDescription('Run one of Abstract Integration Processes');
        $this->addArgument('process_code', InputArgument::REQUIRED, 'Process Code');
        $this->addArgument('run_params', InputArgument::OPTIONAL, 'Run Options');

        $this->setHelp(
            <<<HELP
This command runs one of Abstract Integration Processes. 
To run:
      <comment>%command.full_name% process_code</comment>
To run with options:
      <comment>%command.full_name% process_code option_name1=option_value1:option_name2=option_value2 ...</comment>
      
HELP
        );

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
        $processCode = $input->getArgument('process_code');
        $runOptions = $input->getArgument('run_params');

        $runOptionsPrepared = [];
        if ($runOptions = trim($runOptions)) {
            $optionsArr = explode(':', $runOptions);
            foreach ($optionsArr as $oneOption) {
                $kv = explode('=', $oneOption);
                if (count($kv) !== 2) {
                    $output->writeln('<error>Wrong Options Format. Use --help for checking run command format</error>');
                    return;
                }
                $runOptionsPrepared[$kv[0]] = $kv[1];
            }
        }

        $this->appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_GLOBAL,
            [$this, 'executeProcess'],
            [$processCode, $runOptionsPrepared, $output]
        );
    }

    /**
     * @param string $processCode
     * @param array $runOptionsPrepared
     * @param OutputInterface $output
     * @return void
     */
    public function executeProcess($processCode, array $runOptionsPrepared, OutputInterface $output)
    {
        $result = $this->engineFactory
            ->create(
                [
                    'processCode' => $processCode,
                    'initiator' => 'CLI',
                    'runOptions' => $runOptionsPrepared
                ]
            )
            ->run();

        if ($result->getResult()) {
            $output->writeln('Finished Successfully');
        } else {
            $output->writeln('<error>Finished with Error</error>');
            $output->writeln((string)$result->getMessage());
        }
    }
}
