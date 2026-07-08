<?php

namespace Digidirect\Localization\Model\Import;

interface ParserProcessorInterface
{
    /**
     * @param string $fileData
     * @return array
     */
    public function execute($fileData);
}
