<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Popups;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Plumrocket\Newsletterpopup\Api\PopupThemeRepositoryInterface;

class LoadTemplate extends Action
{
    public const ADMIN_RESOURCE = 'Plumrocket_Newsletterpopup::popups';

    /**
     * @var \Plumrocket\Newsletterpopup\Api\PopupThemeRepositoryInterface
     */
    private $popupThemeRepository;

    /**
     * @param \Magento\Backend\App\Action\Context                           $context
     * @param \Plumrocket\Newsletterpopup\Api\PopupThemeRepositoryInterface $popupThemeRepository
     */
    public function __construct(
        Context $context,
        PopupThemeRepositoryInterface $popupThemeRepository
    ) {
        parent::__construct($context);
        $this->popupThemeRepository = $popupThemeRepository;
    }

    /**
     * Get theme details.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute(): ResultInterface
    {
        if (! $this->getRequest()->getParam('id')) {
            return $this->resultFactory
                ->create(ResultFactory::TYPE_JSON)
                ->setHttpResponseCode(400)
                ->setData(['message' => 'Required params "id" is missing']);
        }

        try {
            $theme = $this->popupThemeRepository->getById((int) $this->getRequest()->getParam('id'));
            return $this->resultFactory
                ->create(ResultFactory::TYPE_JSON)
                ->setData($theme->getData());
        } catch (NoSuchEntityException $e) {
            return $this->resultFactory
                ->create(ResultFactory::TYPE_JSON)
                ->setHttpResponseCode(404)
                ->setData(['message' => $e->getMessage()]);
        }
    }
}
