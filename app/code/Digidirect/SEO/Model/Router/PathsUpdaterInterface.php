<?php

namespace Digidirect\SEO\Model\Router;

/**
 * Interface PathsUpdaterInterface
 *
 * @package Digidirect\SEO\Model\Router
 */
interface PathsUpdaterInterface
{
    /**
     * @param array $paths
     *
     * @return array
     */
    public function update(array $paths);
}
