<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Plumrocket\Newsletterpopup\Model\Popup\GetActive;
use Plumrocket\Newsletterpopup\ViewModel\Popup\Renderer;

/**
 * @since 4.6.0
 */
class Popup extends Action
{

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\GetActive
     */
    private $getActivePopup;

    /**
     * @var \Plumrocket\Newsletterpopup\ViewModel\Popup\Renderer
     */
    private $popupRenderer;

    /**
     * @param \Magento\Framework\App\Action\Context                $context
     * @param \Plumrocket\Newsletterpopup\Model\Popup\GetActive    $getActivePopup
     * @param \Plumrocket\Newsletterpopup\ViewModel\Popup\Renderer $renderer
     */
    public function __construct(
        Context $context,
        GetActive $getActivePopup,
        Renderer $renderer
    ) {
        parent::__construct($context);
        $this->getActivePopup = $getActivePopup;
        $this->popupRenderer = $renderer;
    }

    /**
     * Get popup HTML,CSS and setting.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(): ResultInterface
    {
        try {
            $popup = $this->getActivePopup->execute(
                $this->getRequest()->getParam('area', ''),
                (int) $this->getRequest()->getParam('id', 0)
            );
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(
                [
                    'success' => true,
                    'html' => $this->popupRenderer->render($popup),
                    'settings' => $this->popupRenderer->getJsSettings($popup),
                ]
            );
        } catch (NoSuchEntityException $e) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(
                ['success' => false, 'message' => 'This popup is unavailable.']
            );
        } catch (NotFoundException $e) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(
                ['success' => false, 'message' => 'No popups available for current user and device.']
            );
        }
    }
}
