<?php
namespace Digidirect\LayeredNavigation\Plugin\Admin;

use Digidirect\LayeredNavigation\Api\Data\FilterSettingInterface;
use Digidirect\LayeredNavigation\Api\FilterSettingRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class AttributePlugin
{
    /**
     * @var FilterSettingRepositoryInterface
     */
    protected $filterSettingRepository;

    /**
     * AttributePlugin constructor.
     * @param FilterSettingRepositoryInterface $filterSettingRepository
     */
    public function __construct(FilterSettingRepositoryInterface $filterSettingRepository)
    {
        $this->filterSettingRepository = $filterSettingRepository;
    }

    /**
     * @param Attribute $subject
     * @param \Closure $proceed
     * @return mixed
     * @throws \Exception
     */
    public function aroundSave(Attribute $subject, \Closure $proceed)
    {
        if (!$subject->hasData(FilterSettingInterface::FILTER_CODE)) {
            return $proceed();
        }

        $filterCode = $subject->getAttributeCode();
        $filterSetting = $this->filterSettingRepository->loadByFilterCode($filterCode);
        $filterSetting->addData($subject->getData());
        $currentFilterCode = $filterSetting->getFilterCode();
        if (empty($currentFilterCode)) {
            $filterSetting->setFilterCode('attr_' . $filterCode);
        }

        $this->filterSettingRepository->save($filterSetting);
        return $proceed();
    }
}
