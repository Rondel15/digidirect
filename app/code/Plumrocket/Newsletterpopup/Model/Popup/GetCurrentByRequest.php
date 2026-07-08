<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup;

use Magento\Framework\App\RequestInterface;
use Plumrocket\Newsletterpopup\Api\Data\PopupInterface;

/**
 * @since 4.0.0
 */
class GetCurrentByRequest
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\GetActive
     */
    private $getActive;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Plumrocket\Newsletterpopup\Model\Popup\GetActive $getActive
     * @param \Magento\Framework\App\RequestInterface           $request
     */
    public function __construct(GetActive $getActive, RequestInterface $request)
    {
        $this->getActive = $getActive;
        $this->request = $request;
    }

    /**
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupInterface|\Plumrocket\Newsletterpopup\Model\Popup
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute(): PopupInterface
    {
        return $this->getActive->execute(
            (string) $this->request->getParam('area'),
            (int) $this->request->getParam('id') // can be in request in manual mode
        );
    }
}
