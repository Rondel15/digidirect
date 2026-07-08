<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\Collection;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\CollectionFactory;
use Digidirect\AbstractEntity\Model\ResourceModel\Relation as RelationModel;
use Digidirect\AbstractEntity\Ui\DataProvider\Form\Modifier\Additional;
use Digidirect\Utilities\Helper\AutoComplete;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DB\Helper;

/**
 * Class EntitySearch
 * @package Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity
 */
class EntitySearch extends Action
{
    /**
     * @var Additional
     */
    protected $relation;

    /**
     * @var RelationModel
     */
    protected $collectionFactory;

    /**
     * @var AutoComplete
     */
    protected $autoCompleteHelper;

    /**
     * @var Helper
     */
    protected $dbHelper;

    /**
     * EntitySearch constructor.
     * @param Context           $context
     * @param CollectionFactory $collectionFactory
     * @param RelationModel     $relation
     * @param AutoComplete      $autoCompleteHelper
     * @param Helper            $dbHelper
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        RelationModel $relation,
        AutoComplete $autoCompleteHelper,
        Helper $dbHelper
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->relation = $relation;
        $this->autoCompleteHelper = $autoCompleteHelper;
        $this->dbHelper = $dbHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $options = [];
        $currAttrSetId = $this->getAttributeSetId();
        if ($currAttrSetId && $searchQuery = $this->getRequest()->getParam('name')) {
            $collection = $this->collectionFactory->create();
            $collection
                ->addAttributeToSelect(AbstractEntityInterface::NAME)
                ->addFieldToFilter(
                    AbstractEntityInterface::ATTRIBUTE_SET_ID,
                    $this->relation->getParentAttributeSetId($currAttrSetId)
                );

            $this->addSearchFilter($collection, $searchQuery);
            $options = $collection->toArray();
        }

        return $this->prepareResult($options);
    }

    /**
     * @param Collection $collection
     * @param string     $searchQuery
     * @return $this
     */
    protected function addSearchFilter(Collection $collection, string $searchQuery)
    {
        $escapedQuery = $this->dbHelper->escapeLikeValue($searchQuery, ['position' => 'any']);
        $collection->addFieldToFilter(AbstractEntityInterface::NAME, ['like' => $escapedQuery]);

        return $this;
    }

    /**
     * @return null
     */
    protected function getAttributeSetId()
    {
        $additionalData = $this->autoCompleteHelper->getAjaxAdditionalData();

        return $additionalData[AbstractEntityInterface::ATTRIBUTE_SET_ID] ?? null;
    }

    /**
     * @param array $options
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function prepareResult(array $options)
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($options);
        return $result;
    }
}
