<?php

namespace Digidirect\FreeGift\Plugin;

use Digidirect\FreeGift\Api\RuleRepositoryInterface;
use Digidirect\FreeGift\Api\Data\RuleInterface;

class SalesRule
{
    /**
     * @var RuleRepositoryInterface
     */
    protected $_ruleRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * SalesRule constructor.
     *
     * @param RuleRepositoryInterface $ruleRepository
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_ruleRepository = $ruleRepository;
        $this->_request = $request;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $subject
     * @param \Magento\SalesRule\Model\Rule $result
     * @return mixed
     */
    public function afterSave(\Magento\SalesRule\Model\Rule $subject, $result)
    {
        $freeGiftData = $this->_request->getParam('freegiftrule');
        if ($subject->getId() && $freeGiftData) {
            $freeGiftRule = $this->_ruleRepository->loadBySalesrule($subject);
            $freeGiftRule->addData($freeGiftData);
            $freeGiftRule->setData(RuleInterface::FIELD_SALESRULE_ID, $subject->getId());
            $this->_ruleRepository->save($freeGiftRule);
        }

        return $result;
    }
}
