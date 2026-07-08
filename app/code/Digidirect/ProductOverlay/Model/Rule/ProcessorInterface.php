<?php
namespace Digidirect\ProductOverlay\Model\Rule;

use Digidirect\ProductOverlay\Model\Overlays;

/**
 * Interface ProcessorInterface
 *
 * @package Digidirect\ProductOverlay\Model\Rule
 */
interface ProcessorInterface
{
    /**
     * @param Overlays $overlay
     * @return mixed
     */
    public function isApplicable(Overlays $overlay);
}
