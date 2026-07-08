<?php
namespace Digidirect\SEO\Plugin\Magento\Customer\Block;

use Digidirect\SEO\Helper\TrailingSlash;

/**
 * Class SectionConfig
 */
class SectionConfig
{
    /**
     * @var TrailingSlash
     */
    protected $helper;

    /**
     * Book constructor.
     *
     * @param TrailingSlash $trailingSlashHelper
     */
    public function __construct(TrailingSlash $trailingSlashHelper)
    {
        $this->helper = $trailingSlashHelper;
    }

    /**
     * @param \Magento\Customer\Block\SectionConfig $block
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetUrl(\Magento\Customer\Block\SectionConfig $block, $result)
    {
        if (!$this->helper->isTrailingSlashEnabled()) {
            return $this->helper->addTrailingSlash($result);
        }
        return $result;
    }
}
