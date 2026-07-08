<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Block\Widget;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Plumrocket\Newsletterpopup\Api\PopupRepositoryInterface;
use Plumrocket\Newsletterpopup\Block\Popup as PopupBlock;
use Plumrocket\Newsletterpopup\Helper\Config;
use Plumrocket\Newsletterpopup\ViewModel\Popup\Renderer;

/**
 * @method int getPopupId()
 * @method null|\Plumrocket\Newsletterpopup\Model\Popup getPopup()
 * @method null|\Plumrocket\Newsletterpopup\Block\Popup getPopupBlock()
 *
 * @method $this setPopup(\Plumrocket\Newsletterpopup\Api\Data\PopupInterface $popup)
 * @method $this setPopupBlock($popupBlock)
 *
 * @since 4.0.0
 */
class Form extends Template implements BlockInterface
{

    public const CSS_CLASS_NAME = 'pr-mode-form';

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @var \Plumrocket\Newsletterpopup\Api\PopupRepositoryInterface
     */
    private $popupRepository;

    /**
     * @var \Plumrocket\Newsletterpopup\ViewModel\Popup\Renderer
     */
    private $popupRenderer;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context         $context
     * @param \Plumrocket\Newsletterpopup\Helper\Config                $config
     * @param \Plumrocket\Newsletterpopup\Api\PopupRepositoryInterface $popupRepository
     * @param \Plumrocket\Newsletterpopup\ViewModel\Popup\Renderer     $popupRenderer
     * @param \Magento\Framework\Serialize\SerializerInterface         $serializer
     * @param array                                                    $data
     */
    public function __construct(
        Context $context,
        Config $config,
        PopupRepositoryInterface $popupRepository,
        Renderer $popupRenderer,
        SerializerInterface $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->popupRepository = $popupRepository;
        $this->popupRenderer = $popupRenderer;
        $this->serializer = $serializer;
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        if ($this->initPopup() && $this->getPopup()->getStatus()) {
            $this->initPopupBlock();

            if (! $this->getTemplate()) {
                $this->setTemplate('widget/form.phtml');
            }
        }

        return parent::_beforeToHtml();
    }

    /**
     * @return $this
     */
    private function initPopupBlock()
    {
        /** @var \Plumrocket\Newsletterpopup\Block\Popup $popupBlock */
        $popupBlock = $this->getLayout()->createBlock(PopupBlock::class);
        $popupBlock->setPopup($this->getPopup());
        $popupBlock->noAnimation();
        $popupBlock->setTemplate('templates/prnewsletterpopup-system-template.phtml');
        $this->setPopupBlock($popupBlock);

        return $this;
    }

    /**
     * @return bool
     */
    private function initPopup(): bool
    {
        try {
            $this->setPopup($this->popupRepository->getById((int) $this->getPopupId()));
            return true;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getJsonConfig(): string
    {
        return $this->serializer->serialize(
            $this->popupRenderer->getJsSettings($this->getPopup())
        );
    }

    /**
     * Disable render if module is disabled.
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        if (! $this->config->isModuleEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
}
