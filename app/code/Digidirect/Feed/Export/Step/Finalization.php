<?php

namespace Digidirect\Feed\Export\Step;

use Digidirect\Feed\Export\Context;
use Digidirect\Feed\Helper\Io;
use Digidirect\Feed\Model\Config;

class Finalization extends AbstractStep
{
    /**
     * @var Io
     */
    protected $io;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Finalization constructor.
     * @param Context $context
     * @param Config $config
     * @param Io $io
     */
    public function __construct(
        Context $context,
        Config $config,
        Io $io
    ) {
        $this->io = $io;
        $this->config = $config;

        parent::__construct($context);
    }

    /**
     * Copy temp feed file to regular place
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->isReady()) {
            $this->beforeExecute();
        }

        $feed = $this->context->getFeed();
        $tmpPath = $this->config->getTmpPath() . DIRECTORY_SEPARATOR . $feed->getId() . '.dat';
        $targetPath = $this->config->getBasePath() . DIRECTORY_SEPARATOR . $this->context->getFilename();

        $this->io->copy($tmpPath, $targetPath);

        $this->index++;
    }
}
