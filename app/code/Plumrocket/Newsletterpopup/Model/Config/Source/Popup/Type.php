<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source\Popup;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Asset\Repository;

class Type implements OptionSourceInterface
{
    const MODAL = 'modal';
    const WIDGET_TEMPLATE = 'widget_template';

    /**
     * @var Repository
     */
    private $viewAssetRepository;

    /**
     * ReviewPageStructure constructor.
     *
     * @param Repository $viewAssetRepository
     */
    public function __construct(Repository $viewAssetRepository)
    {
        $this->viewAssetRepository = $viewAssetRepository;
    }

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::MODAL,
                'label' => __('Popup Window'),
                'image' => $this->viewAssetRepository->getUrl(
                    'Plumrocket_Newsletterpopup::images/popup/type-modal.png'
                ),
            ],
            [
                'value' => self::WIDGET_TEMPLATE,
                'label' => __('Widget Template'),
                'image' => $this->viewAssetRepository->getUrl(
                    'Plumrocket_Newsletterpopup::images/popup/type-widget-template.png'
                ),
            ],
        ];
    }

    public function toOptionHash(): array
    {
        $options = [];
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = $option['label'];
        }

        return $options;
    }
}
