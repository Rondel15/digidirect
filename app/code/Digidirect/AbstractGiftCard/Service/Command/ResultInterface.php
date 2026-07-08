<?php

namespace Digidirect\AbstractGiftCard\Service\Command;

/**
 * Interface ResultInterface
 * @package Digidirect\AbstractGiftCard\Service\Command
 * @api
 */
interface ResultInterface
{
    /**
     * Returns result interpretation
     *
     * @return mixed
     */
    public function get();
}
