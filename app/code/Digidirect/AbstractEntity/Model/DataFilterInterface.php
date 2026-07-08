<?php

namespace Digidirect\AbstractEntity\Model;

/**
 * Interface DataFilterInterface
 * @package Digidirect\AbstractEntity\Model
 */
interface DataFilterInterface
{
    /**
     * @param array $data
     * @return void
     */
    public function execute(array &$data);
}
