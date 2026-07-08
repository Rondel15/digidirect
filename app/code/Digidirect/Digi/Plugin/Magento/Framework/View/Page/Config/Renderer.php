<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Digidirect\Digi\Plugin\Magento\Framework\View\Page\Config;

use Digidirect\Digi\Helper\Canonical;

/**
 * Class Renderer
 * @package Digidirect\Digi\Plugin\Magento\Framework\View\Page\Config
 */
class Renderer
{
    /**
     * @var Canonical
     */
    private $canonicalHelper;

    public function __construct(
        Canonical $canonicalHelper
    ) {
        $this->canonicalHelper = $canonicalHelper;
    }

    public function beforeRenderAssets(\Magento\Framework\View\Page\Config\Renderer $subject, $resultGroups = [])
    {
        $this->canonicalHelper->setCanonicalAlias();
        return [$resultGroups];
    }
}
