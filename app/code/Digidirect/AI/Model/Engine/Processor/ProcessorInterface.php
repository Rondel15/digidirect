<?php
namespace Digidirect\AI\Model\Engine\Processor;

/**
 * Interface ProcessorInterface
 *
 * @package Digidirect\AI\Model\Engine\Processor
 */
interface ProcessorInterface
{
    /**
     * Process integration
     *
     * @return boolean
     */
    public function process();

    /**
     * @return mixed
     */
    public function getResult();
}
