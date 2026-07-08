<?php

namespace Digidirect\Feed\Export\Step;

use Magento\Framework\App\ResourceConnection;
use Digidirect\Feed\Export\Context;
use Digidirect\Feed\Model\RuleRepository;

class Filtration extends AbstractStep
{
    /**
     * @var RuleRepository
     */
    protected $ruleRepository;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var StepFactory
     */
    protected $stepFactory;

    /**
     * Filtration constructor.
     * @param Context $context
     * @param RuleRepository $ruleRepository
     * @param ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        RuleRepository $ruleRepository,
        ResourceConnection $resource
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->resource = $resource;
        $this->stepFactory = $context->getStepFactory();

        parent::__construct($context);
    }

    /**
     * Add assigned rules as sub steps
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        if ($this->context->isTestMode()) {
            return parent::beforeExecute();
        }

        foreach ($this->context->getFeed()->getRuleIds() as $ruleId) {
            $rule = $this->ruleRepository->getById($ruleId);
            $this->addStep(
                $this->stepFactory->create('Filtration\Rule', ['data' => ['rule_id' => $ruleId]])
                    ->setName($rule->getName())
            );
        }

        return parent::beforeExecute();
    }

    /**
     * Merge rules
     *
     * {@inheritdoc}
     */
    public function afterExecute()
    {
        $feed = $this->context->getFeed();

        $feedId = (int)$feed->getId();
        $ruleIds = $feed->getRuleIds();
        $ruleIdsCount = count($ruleIds);

        $connection = $this->resource->getConnection();

        $feedProductTable = $this->resource->getTableName('digidirect_feed_feed_product');
        $connection->delete($feedProductTable, $connection->quoteInto('feed_id = ?', $feedId));

        if ($ruleIdsCount > 0) {
            $columns = [
                'product_id' => 'product_id',
                'feed_id' => new \Zend_Db_Expr($feedId),
            ];

            $select = $connection->select();
            $select->from($this->resource->getTableName('digidirect_feed_rule_product'), $columns)
                ->where('rule_id IN (?)', $ruleIds);

            if ($ruleIdsCount > 1) {
                $select->group('product_id')
                    ->having('count(product_id) = ?', $ruleIdsCount);
            }

            $insertQuery = $select->insertFromSelect($feedProductTable, array_keys($columns));
            $connection->query($insertQuery);
        }

        return parent::afterExecute();
    }
}
