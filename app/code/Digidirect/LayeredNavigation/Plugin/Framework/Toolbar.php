<?php
namespace Digidirect\LayeredNavigation\Plugin\Framework;

use Digidirect\LayeredNavigation\Helper\UrlBuilder;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Toolbar
{
    /** @var  Registry */
    protected $registry;

    /**
     * ToolbarPlugin constructor.
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param Template $subject
     * @param array $params
     * @return array
     */
    public function beforeGetPagerUrl(Template $subject, $params = [])
    {
        $seoParsed = $this->registry->registry(UrlBuilder::SEO_PARSED_PARAMS);
        if (is_array($seoParsed)) {
            $params = array_merge($seoParsed, $params);
        }

        return [$params];
    }
}
