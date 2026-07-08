<?php

namespace Digidirect\AbstractGiftCard\Service\Request;

/**
 * Interface BuilderInterface
 * @package Digidirect\AbstractGiftCard\Service\Request
 * @api
 */
interface BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject);
}
