<?php

namespace Digidirect\Feed\Export;

use Digidirect\Feed\Model\Feed;

class Handler
{
    /**
     * Constructor
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * Set fed model
     *
     * @param Feed $feed
     * @return $this
     */
    public function setFeed(Feed $feed)
    {
        $this->context->setFeed($feed);
        $this->context->load();

        return $this;
    }

    /**
     * Execute active step
     *
     * @return string
     */
    public function execute()
    {
        $this->context->execute();

        return $this->getStatus();
    }

    /**
     * Set feed filename
     *
     * @param string $file
     * @return $this
     */
    public function setFilename($file)
    {
        $this->context->setFilename($file);

        return $this;
    }

    /**
     * Enable test mode
     *
     * @return $this
     */
    public function enableTestMode()
    {
        $this->context->enableTestMode();

        return $this;
    }

    /**
     * Reset
     *
     * @return $this
     */
    public function reset()
    {
        $this->context->reset();

        return $this;
    }

    /**
     * Status of all exprting
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->context->getRootStep()->getStatus();
    }

    /**
     * Time since start generation (seconds)
     *
     * @return int
     */
    public function getTimeSinceStart()
    {
        return microtime(true) - $this->context->getCreatedAt();
    }

    /**
     * Convert steps to json
     *
     * @return array
     */
    public function toJson()
    {
        return $this->context->getRootStep()->toJson();
    }

    /**
     * Convert steps to string
     *
     * @return string
     */
    public function toString()
    {
        return $this->context->getRootStep()->toString();
    }
}
