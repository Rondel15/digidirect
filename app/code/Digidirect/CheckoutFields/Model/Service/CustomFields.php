<?php

namespace Digidirect\CheckoutFields\Model\Service;

use Digidirect\CheckoutFields\Api\CustomFieldsInterface;
use Digidirect\CheckoutFields\Helper\Xml\Fields\Parser;
use Digidirect\CheckoutFields\Model\OrderFieldValueFactory;
use Digidirect\CheckoutFields\Model\QuoteFieldValueFactory;
use Digidirect\CheckoutFields\Model\ResourceModel\QuoteFieldValue\Collection;
use Digidirect\Utilities\Helper\Data as UtilitiesHelper;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CustomFields
 *
 * @package Digidirect\CheckoutFields\Model\Service
 */
class CustomFields implements CustomFieldsInterface
{
    /**
     * @var QuoteFieldValueFactory
     */
    protected $quoteFieldValueModel;

    /**
     * @var OrderFieldValueFactory
     */
    protected $orderFieldValueModel;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var UtilitiesHelper
     */
    protected $utilitiesHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * CustomFields constructor.
     *
     * @param QuoteFieldValueFactory $quoteFieldValueModel
     * @param OrderFieldValueFactory $orderFieldValueModel
     * @param Parser $parser
     * @param Session $checkoutSession
     * @param Collection $collection
     * @param UtilitiesHelper $utilitiesHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        QuoteFieldValueFactory $quoteFieldValueModel,
        OrderFieldValueFactory $orderFieldValueModel,
        Parser $parser,
        Session $checkoutSession,
        Collection $collection,
        UtilitiesHelper $utilitiesHelper,
        ManagerInterface $eventManager
    ) {
        $this->quoteFieldValueModel = $quoteFieldValueModel;
        $this->orderFieldValueModel = $orderFieldValueModel;
        $this->parser = $parser;
        $this->checkoutSession = $checkoutSession;
        $this->utilitiesHelper = $utilitiesHelper;
        $this->eventManager = $eventManager ?: ObjectManager::getInstance()->get(ManagerInterface::class);
    }

    /**
     *  Save custom checkout fields to custom tables
     *
     * @param mixed $params
     * @return bool
     * @throws LocalizedException
     */
    public function save($params)
    {
        if ($this->checkoutSession->getObserverFlag()) {
            $this->checkoutSession->unsObserverFlag();
            // We should have a new order ID already
            $order = $this->checkoutSession->getLastRealOrder();
            if ($order && $order->getId()) {
                // we can store field directly in order_field_value
                $this->saveFields($params, $this->orderFieldValueModel, 'order_id', $order);

                return true;
            }
        }

        /**
         * @var $model \Digidirect\CheckoutFields\Model\QuoteFieldValue
         */
        $model = $this->quoteFieldValueModel->create();
        $quote = $this->checkoutSession->getQuote();
        try {
            $quoteId = $quote->getId() ?? $this->checkoutSession->getLastRealOrder()->getQuoteId();
        } catch (\Exception $e) {
            throw new LocalizedException(__('Something went wrong with session.'));
        }

        $model->cleanDataBeforeSave($quoteId);
        $this->saveFields($params, $this->quoteFieldValueModel, 'quote_id', $quote);

        return true;
    }

    /**
     * @param array $params
     * @param QuoteFieldValueFactory|OrderFieldValueFactory $modelFactory
     * @param string $entityIdField
     * @param \Magento\Quote\Model\Quote|\Magento\Sales\Model\Order $entity
     */
    protected function saveFields($params, $modelFactory, $entityIdField, $entity)
    {
        $fields = $this->parser->getFields();

        $savedFields = [];
        foreach ($params as $code => $values) {
            if (isset($fields[$code])) {
                $model = $modelFactory->create();
                $value = $values['value'] ?? '';
                $frontendInput = $fields[$code]['frontend_input'] ?? '';
                $preparedValue = $this->prepareTextValue($value, $frontendInput);

                $data = [
                    'code' => $values['label'] ?? null,
                    $entityIdField => $entity->getId(),
                    'value' => serialize($preparedValue),
                    'field_id' => $code,
                ];
                $model->setData($data);
                $model->getResource()->save($model);

                $data['model'] = $model;
                $data['raw_value'] = $value;
                $data['raw_prepared_value'] = $preparedValue;
                $savedFields[$code] = new DataObject($data);
            }
        }

        if ($savedFields) {
            $this->eventManager->dispatch(
                'checkoutfields_save_fields_after',
                [
                    'fields' => $savedFields,
                    'checkout_object' => $entity,
                    'params' => $params
                ]
            );
        }
    }

    /**
     * Prepare Text Value to serialization
     *
     * @param string $value
     * @param string $fieldType
     * @return string
     */
    protected function prepareTextValue($value, $fieldType)
    {
        if (!empty($value) && in_array($fieldType, ['text', 'textarea'])) {
            $value = $this->utilitiesHelper->replace4byte($value);
        }

        return $value;
    }
}
