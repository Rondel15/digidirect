<?php

namespace Digidirect\Blog\Model\Config\Source;

use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory as ThemeFactory;

/**
 * Class Theme
 */
class Theme implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ThemeFactory
     */
    protected $themeFactory;

    /**
     * Theme constructor.
     * @param ThemeFactory $themeFactory
     */
    public function __construct(
        ThemeFactory $themeFactory
    ) {
        $this->themeFactory = $themeFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $themeList = $this->themeFactory->create();

        $returnArray[0] = '-- Please Select --';
        foreach ($themeList as $item) {
            $returnArray[$item->getThemeId()] = $item->getThemeTitle();
        }

        return $returnArray;
    }
}
