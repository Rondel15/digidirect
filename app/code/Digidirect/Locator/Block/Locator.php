<?php

namespace Digidirect\Locator\Block;

use Digidirect\Locator\Helper\DefaultConfiguration;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Digidirect\Locator\Model\Locator as LocatorModel;
use Magento\Catalog\Model\Template\Filter as AttributeFilter;

class Locator extends Template
{
    /**
     * @var LocatorModel
     */
    protected $locator;

    /**
     * @var string
     */
    protected $defaultClass = \Digidirect\Locator\Helper\DefaultConfiguration::class;

    /**
     * @var DefaultConfiguration|null
     */
    protected $configHelper;

    /**
     * @var AttributeFilter
     */
    protected $attributeFilter;

    /**
     * @var array
     */
    protected $callbacks = [];

    /**
     * Locator constructor.
     * @param Context $context
     * @param LocatorModel $locator
     * @param array $data
     */
    public function __construct(
        Context $context,
        LocatorModel $locator,
        AttributeFilter $attributeFilter,
        array $data
    ) {
        $this->locator = $locator;
        $this->attributeFilter = $attributeFilter;
        parent::__construct($context, $data);
        $filterable = $this->getData('filterable_attributes');
        if (!empty($filterable)) {
            $this->setFilterCallbacks($filterable);
        }
    }

    /**
     * Get callbacks functions
     *
     * @return array
     */
    protected function getCallbacks()
    {
        return $this->callbacks;
    }

    /**
     * Set callback functions for data filtering for specific attribute
     *
     * @param $attrName
     * @param $function
     */
    public function setFilterCallback($attrName, $function)
    {
        $this->callbacks[$attrName] = $function;
    }

    /**
     * Set callback functions for data filtering
     *
     * @param $filterable
     */
    public function setFilterCallbacks($filterable)
    {
        if (!empty($filterable)) {
            foreach ($filterable as $attrName) {
                $this->setFilterCallback(
                    $attrName,
                    $this->filter()
                );
            }
        }
    }

    /**
     * Add callbacks functions to entities
     *
     * @param $data
     * @return mixed
     */
    protected function addCallbacksToEntitys($data)
    {
        if (isset($data['entities'])) {
            foreach ($data['entities'] as $key => $entity) {
                $data['entities'][$key]['callbacks'] = $this->getCallbacks();
            }
        }

        return $data;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getEntities()
    {
        $data = $this->addCallbacksToEntitys($this->getData());

        return json_encode($this->locator->getEntities(
            $data['entities'],
            $this->getRequest()->getParams()
        ));
    }

    /**
     * @return DefaultConfiguration|mixed
     * @throws LocalizedException
     * @since 1.2.0
     */
    public function getConfigHelper()
    {
        if (null === $this->configHelper) {
            $helperClass = $this->getData('config_helper');
            if (!$helperClass) {
                $helperClass = $this->defaultClass;
            }
            $configHelper = ObjectManager::getInstance()->get($helperClass);
            if (!($configHelper instanceof DefaultConfiguration)) {
                throw new LocalizedException(
                    __('Configuration helper must be an instance of %1', DefaultConfiguration::class)
                );
            }

            $this->configHelper = $configHelper;
        }
        return $this->configHelper;
    }

    /**
     * Creation of callback fuction for data filtering
     *
     * @param string $string
     * @return \Closure
     */
    public function filter($str = '')
    {
        return function ($str = '') {
            if ($str) {
                return $this->attributeFilter->filter($str);
            }

            return $str;
        };
    }
}
