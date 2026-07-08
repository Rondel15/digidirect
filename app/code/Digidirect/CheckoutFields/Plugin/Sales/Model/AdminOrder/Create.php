<?php

namespace Digidirect\CheckoutFields\Plugin\Sales\Model\AdminOrder;

use Digidirect\CheckoutFields\Api\QuoteFieldValueRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\AdminOrder\Create as SubjectCreate;

/**
 * Class Create
 *
 * @package Digidirect\CheckoutFields\Plugin\Sales\Model\AdminOrder
 */
class Create
{
    /**
     * @var QuoteFieldValueRepositoryInterface
     */
    protected $quoteFieldValueRepository;

    /**
     * Create constructor.
     *
     * @param QuoteFieldValueRepositoryInterface $quoteFieldValueRepository
     */
    public function __construct(QuoteFieldValueRepositoryInterface $quoteFieldValueRepository)
    {
        $this->quoteFieldValueRepository = $quoteFieldValueRepository;
    }

    /**
     * save checkout fields to quote
     *
     * @param SubjectCreate $subject
     * @param SubjectCreate $result
     * @param               $data
     *
     * @return SubjectCreate
     * @throws LocalizedException
     */
    public function afterImportPostData(SubjectCreate $subject, SubjectCreate $result, $data)
    {
        $quote = $subject->getQuote();
        if (!$quote instanceof CartInterface) {
            return $result;
        }

        $params = isset($data['additional']) ? $data['additional'] : [];
        if ($params) {
            $this->quoteFieldValueRepository->saveToQuote($subject->getQuote(), $params);
        }

        return $result;
    }
}
