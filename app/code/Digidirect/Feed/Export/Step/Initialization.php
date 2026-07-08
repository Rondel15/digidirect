<?php

namespace Digidirect\Feed\Export\Step;

use Digidirect\Feed\Export\Context;
use Digidirect\Feed\Helper\Io;
use Digidirect\Feed\Model\Config;

class Initialization extends AbstractStep
{
    /**
     * @var \Digidirect\Feed\Helper\Io
     */
    protected $io;

    /**
     * @var \Digidirect\Feed\Model\Config
     */
    protected $config;

    /**
     * Initialization constructor.
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
     * Remove state and temp feed file
     * {@inheritdoc}
     */
    public function execute()
    {
        $tmpPath = $this->config->getTmpPath();

        $this->io->unlink($this->context->getStateFile());
        $this->io->unlink($tmpPath . DIRECTORY_SEPARATOR . $this->context->getFeed()->getId() . '.dat');

        $this->index = 1;
    }
}
