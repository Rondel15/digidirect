<?php

namespace Digidirect\Feed\Export\Step\Filtration;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Digidirect\Feed\Export\Context;
use Digidirect\Feed\Export\Step\AbstractStep;
use Digidirect\Feed\Model\RuleRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class Rule extends AbstractStep
{
    /**
     * Rule Factory
     *
     * @var RuleRepository
     */
    protected $ruleRepository;

    /**
     * Product Collection Factory
     *
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Rule
     *
     * @var \Digidirect\Feed\Model\Rule
     */
    protected $rule;

    /**
     * Rule constructor.
     * @param Context $context
     * @param RuleRepository $ruleRepository
     * @param ProductCollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        RuleRepository $ruleRepository,
        ProductCollectionFactory $productCollectionFactory,
        $data = []
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->productCollectionFactory = $productCollectionFactory;

        try {
            $this->rule = $ruleRepository->getById($data['rule_id']);
        } catch (NoSuchEntityException $e) {
            /*
             * State file contains non existent filtration Rule. It will be removed on the first initialization step:
             * \Digidirect\Feed\Export\Step\Initialization
             */
        }

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        $return = parent::beforeExecute();

        $this->index = 0;
        $this->length = $this->getProductCollection()->getSize();
        $this->rule->clearProductIds();

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->isReady()) {
            $this->beforeExecute();
        }

        $rule = $this->rule;

        $validIds = [];
        while (!$this->isCompleted()) {
            $collection = $this->getProductCollection();

            $collection->getSelect()->limit(100, $this->index);

            if (!$collection->count()) {
                $this->index++;
                break;
            }

            foreach ($collection as $product) {
                if ($rule->getConditions()->validate($product)) {
                    $validIds[] = $product->getId();
                }

                $this->index++;

                if ($this->context->isTimeout()) {
                    break 2;
                }
            }
        }

        $rule->saveProductIds($validIds);

        if ($this->isCompleted()) {
            $this->afterExecute();
        }
    }

    /**
     * Product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getProductCollection()
    {
        $collection = $this->productCollectionFactory->create()
            ->addStoreFilter($this->context->getFeed()->getStoreId())
            ->setStoreId($this->context->getFeed()->getStoreId());

        return $collection;
    }
}
