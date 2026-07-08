<?php

namespace Digidirect\Blog\Model;

use Digidirect\Blog\Helper\Design;
use Magento\Framework\View\DesignInterface;

/**
 * Class BlogDesign
 */
class BlogDesign
{
    /**
     * @var Design
     */
    protected $designHelper;

    /**
     * @var DesignInterface
     */
    protected $designInterface;

    /**
     * BlogDesign constructor.
     * @param Design $designHelper
     * @param DesignInterface $designInterface
     */
    public function __construct(
        Design $designHelper,
        DesignInterface $designInterface
    ) {
        $this->designHelper = $designHelper;
        $this->designInterface = $designInterface;
    }

    /**
     * @return void
     */
    public function setNewTheme()
    {
        if ($this->designHelper->getSpecialTheme()) {
            $newThemeId = $this->designHelper->getSpecialTheme();
            $this->designInterface->setDesignTheme($newThemeId);
        }
        return;
    }
}
