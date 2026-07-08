<?php
namespace Digidirect\LayeredNavigation\Observer;

use Magento\Framework\DB\Select;

class CategoryFlatLoadBeforeObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Add Url Key to the category flat collection
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $select = $observer->getSelect();
        if ($select instanceof Select) {
            $columns = $select->getPart(Select::COLUMNS);
            $columns[] = ['main_table', 'url_key', null];
            $select->setPart(Select::COLUMNS, $columns);
        }
        return $this;
    }
}
