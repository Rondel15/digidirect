<?php
declare(strict_types=1);

namespace Digidirect\Digi\Block\SeoBrand;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Digidirect\Digi\Model\SeoBrandDescription as ModelSeoBrand;

/**
 * TODO: rewrite all logic and move part of code info helper
 *
 * Class SeoBrandDescription
 * @package Digidirect\Digi\Block\SeoBrand
 */
class SeoBrandDescription extends Template
{
    /**
     * @var ModelSeoBrand
     */
    private $seoBrandDescription;

    /**
     * SeoBrandDescription constructor.
     * @param Context $context
     * @param ModelSeoBrand $seoBrandDescription
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ModelSeoBrand $seoBrandDescription,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->seoBrandDescription = $seoBrandDescription;
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSeoData(): ?string
    {
        $result = null;
        $seoBrandEntity = $this->seoBrandDescription->getSeoBrandEntity();

        if ($seoBrandEntity && $seoBrandEntity->getId()) {
            $result = $seoBrandEntity->getCategoryBrandDescription();
        }
        return $result;
    }


    /**
     * @return void
     */
    protected function setContentPageTitle()
    {
        // set html page title
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $brandName = $this->seoBrandDescription->getBrandLabel(
                $this->seoBrandDescription->getCurrentOption()
            );
            $pageMainTitle->setPageTitle($this->escapeHtml($brandName));
        }
    }

    /**
     * @param array $filterNames
     * @return void
     */
    protected function setFilterContentPageTitle(array $filterNames)
    {
        // set html page title
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $filterTitle = $this->preparedFilterTitle($filterNames);
            $pageMainTitle->setPageTitle($this->escapeHtml($filterTitle . ': ' . $pageMainTitle->getPageTitle()));
        }
    }

    /**
     * @param array $filterNames
     * @return string
     */
    public function preparedFilterTitle(array $filterNames)
    {
        if (empty($filterNames)) {
            return 'Filter';
        }

        if (count($filterNames) == 1) {
            return array_shift($filterNames);
        }
        return array_pop($filterNames);
    }
    /**
     * @return void
     */
    public function prepareSeoData()
    {
        $result = null;
        try {
            if ($this->seoBrandDescription->isCategoryBrandPage()) {
                $seoBrandEntity = $this->seoBrandDescription->getSeoBrandEntity();
                $this->seoBrandDescription->setDefaultMetaInformation();

                if ($seoBrandEntity && $seoBrandEntity->getId()) {
                    $this->seoBrandDescription->setMetaInformationByEntity($seoBrandEntity);
                }

                $this->setContentPageTitle();
            } else {
                if ($this->seoBrandDescription->isFiltered()) {
                    $this->setFilterContentPageTitle($this->seoBrandDescription->getLastFilterLabel());
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            //nothing
        }
    }


    /**
     * @return bool
     */
    public function isFilterPage()
    {
        return $this->seoBrandDescription->isFilterPage();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->prepareSeoData();
        return $this;
    }
}
