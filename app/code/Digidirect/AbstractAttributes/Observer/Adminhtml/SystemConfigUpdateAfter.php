<?php
namespace Digidirect\AbstractAttributes\Observer\Adminhtml;

use Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface;
use Digidirect\AbstractAttributes\Api\OptionRepositoryInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;

/**
 * Class SystemConfigUpdateAfter
 * @package Digidirect\AbstractAttributes\Observer\Adminhtml
 */
class SystemConfigUpdateAfter implements ObserverInterface
{
    /**
     * @var AbstractAttributeRepositoryInterface
     */
    protected $abstractAttributeRepository;

    /**
     * @var OptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @param AbstractAttributeRepositoryInterface $abstractAttributeRepository
     * @param OptionRepositoryInterface $optionRepository
     */
    public function __construct(
        AbstractAttributeRepositoryInterface $abstractAttributeRepository,
        OptionRepositoryInterface $optionRepository
    ) {
        $this->abstractAttributeRepository = $abstractAttributeRepository;
        $this->optionRepository = $optionRepository;
    }

    /**
     * Execute
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $this->abstractAttributeRepository->regenerateUrlRewrites();
        $this->optionRepository->regenerateUrlRewrites();
        return $this;
    }
}
