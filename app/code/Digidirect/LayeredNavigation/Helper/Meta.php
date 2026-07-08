<?php
namespace Digidirect\LayeredNavigation\Helper;

use Digidirect\LayeredNavigation\Api\Data\FilterSettingInterface;
use Digidirect\LayeredNavigation\Model\Source\IndexMode;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Page\Config;

class Meta extends AbstractHelper
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Meta constructor.
     * @param Context $context
     * @param Data $dataHelper
     */
    public function __construct(Context $context, \Digidirect\LayeredNavigation\Helper\Data $dataHelper)
    {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->request = $context->getRequest();
    }

    /**
     * @return bool
     */
    public function isRobotsControlEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'digidirect_layerednavigation/robots/control_robots',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @param Config $pageConfig
     * @return void
     */
    public function setPageTags(Config $pageConfig)
    {
        $robots = $pageConfig->getRobots();
        if (!$this->isRobotsControlEnabled()) {
            return;
        }

        $index = true;
        $follow = true;
        $appliedFiltersSettings = $this->dataHelper->getSelectedFiltersSettings();
        foreach ($appliedFiltersSettings as $row) {
            /** @var FilterSettingInterface $setting */
            $setting = $row['setting'];

            /** @var FilterInterface $filter */
            $filter = $row['filter'];
            $value = $this->request->getParam($filter->getRequestVar());
            $count = count(explode(UrlParser::ALIAS_DELIMITER, $value));

            if ($setting->getIndexMode() == IndexMode::MODE_NEVER) {
                $index = false;
            } elseif ($setting->getIndexMode() == IndexMode::MODE_SINGLE_ONLY && $count >= 2) {
                $index = false;
            }

            if ($setting->getFollowMode() == IndexMode::MODE_NEVER) {
                $follow = false;
            } elseif ($setting->getFollowMode() == IndexMode::MODE_SINGLE_ONLY && $count >= 2) {
                $follow = false;
            }
        }

        if (!$index) {
            $robots = preg_replace('/\w*index/i', 'noindex', $robots);
        }

        if (!$follow) {
            $robots = preg_replace('/\w*follow/i', 'nofollow', $robots);
        }

        $pageConfig->setRobots($robots);
    }
}
