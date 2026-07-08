<?php
namespace Digidirect\SEO\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Class SetCategoryMetadata
 * @package Digidirect\SEO\Observer
 */
class SetCategoryMetadata implements ObserverInterface
{
    /**
     * @var \Digidirect\SEO\Model\CategoryMetadata
     */
    protected $categoryMetadataProcessor;

    /**
     * SetCategoryMetadata constructor.
     * @param \Digidirect\SEO\Model\CategoryMetadata $processor
     */
    public function __construct(\Digidirect\SEO\Model\CategoryMetadata $processor)
    {
        $this->categoryMetadataProcessor = $processor;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $this->categoryMetadataProcessor->setMetadata($observer->getEvent()->getCategory());
        return $this;
    }
}
