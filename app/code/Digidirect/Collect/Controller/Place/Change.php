<?php

namespace Digidirect\Collect\Controller\Place;

use Digidirect\Collect\Controller\AbstractAction;
use Digidirect\Collect\Helper\Data as CollectHelper;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Change
 *
 * @package Digidirect\Collect\Controller\Place
 */
class Change extends AbstractAction
{
    /**
     * @return \Magento\Framework\Controller\Result\Json $resultJson
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $request = $this->getRequest();
        $params = [
            'quote_item_id' => $request->getParam('quote_item_id'),
            'collect_place_id' => $request->getParam('collect_place_id'),
            'collect_place_storage_name' => $request->getParam('collect_place_storage_name')
        ];

        $deliveryMethod = $this->getRequest()->getParam('deliver_method');
        if ($deliveryMethod != CollectHelper::DELIVERY_TYPE_DELIVER) {
            $deliveryMethod = CollectHelper::DELIVERY_TYPE_COLLECT;
        }

        try {
            $result = $this->_collectHelper->changeQuoteDeliverMethod($params, $deliveryMethod);
        } catch (LocalizedException $e) {
            $this->_collectHelper->logError($e->getMessage());

            return $resultJson->setData(
                [
                    'data' => [],
                    'result' => false,
                    'message' => $e->getMessage()
                ]
            );
        } catch (\Exception $e) {
            $this->_collectHelper->logError($e->getMessage());

            return $resultJson->setData(
                [
                    'data' => [],
                    'result' => false,
                    'message' => $this->getExceptionMessage($e)
                ]
            );
        }

        return $resultJson->setData(
            [
                'data' => $result,
                'result' => $result['result'] ?? false
            ]
        );
    }

    /**
     * GetExceptionMessage
     *
     * @param \Exception $e
     * @return \Magento\Framework\Phrase
     */
    public function getExceptionMessage($e)
    {
        return __('Something went wrong, try later.');
    }
}
