<?php
namespace Digidirect\Blog\Model\Post\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;
use Digidirect\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

/**
 * Class Categories
 */
class Categories implements OptionSourceInterface
{
    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var CategoryCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $currentOptions = [];

    /**
     * Constructor
     *
     * @param CategoryCollectionFactory $collectionFactory
     * @param Escaper $escaper
     */
    public function __construct(CategoryCollectionFactory $collectionFactory, Escaper $escaper)
    {
        $this->collectionFactory = $collectionFactory;
        $this->escaper = $escaper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->generateCurrentOptions();
            $this->options = array_values($this->currentOptions);
        }
        return $this->options;
    }

    /**
     * Generate current options
     *
     * @return void
     */
    protected function generateCurrentOptions()
    {
        $collection = $this->collectionFactory->create();
        $this->currentOptions[] = ['label' => '', 'value' => ''];
        foreach ($collection as $category) {
            $name = $this->escaper->escapeHtml($category->getName());
            $this->currentOptions[$name]['label'] = $name;
            $this->currentOptions[$name]['value'] = $category->getId();
        }
    }
}
