<?php

namespace Digidirect\Feed\Controller\Export;

use Digidirect\Feed\Controller\Export;
use Digidirect\Feed\Model\Config;

class Execute extends Export
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $mode = $this->getRequest()->getParam('mode');

        try {
            $feed = $this->getFeed();
            $handler = $this->exporter->getHandler($feed);

            $result = [
                'success' => true,
                'progress' => [],
            ];

            if (!$mode || $mode == 'new') {
                $handler->reset();
                $result['progress'] = $handler->toJson();
            } else {
                $status = $this->exporter->export($feed);

                $result['status'] = $status;

                if ($status == Config::STATUS_COMPLETED) {
                    $result['progress']['completed'] = [
                        'url' => $feed->getUrl(),
                        'time' => gmdate('H:i:s', $feed->getGeneratedTime())
                    ];
                } else {
                    $result['progress'] = $handler->toJson();
                }
            }
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['progress']['error'] = $e->getMessage();
        }

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();
        $response->representJson(\Zend_Json::encode($result));
    }

    /**
     * {@inheritdoc}
     *
     * Disable keys (request without form key)
     */
    protected function _processUrlKeys()
    {
        return true;
    }
}
