<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\ViewModel\Popup;

use Magento\Framework\Serialize\SerializerInterface;
use Plumrocket\Newsletterpopup\Model\Popup\GetSuccessPage;
use Plumrocket\Newsletterpopup\Model\Popup\Space;

/**
 * @since 4.0.0
 */
class JsConfig
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\GetSuccessPage
     */
    private $getPopupSuccessPage;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\Space
     */
    private $space;

    /**
     * @param \Magento\Framework\Serialize\SerializerInterface       $serializer
     * @param \Plumrocket\Newsletterpopup\Model\Popup\GetSuccessPage $getPopupSuccessPage
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Space          $space
     */
    public function __construct(
        SerializerInterface $serializer,
        GetSuccessPage $getPopupSuccessPage,
        Space $space
    ) {
        $this->serializer = $serializer;
        $this->getPopupSuccessPage = $getPopupSuccessPage;
        $this->space = $space;
    }

    /**
     * @param \Plumrocket\Newsletterpopup\Model\Popup|\Plumrocket\Newsletterpopup\Api\Data\PopupInterface $popup
     * @param array                                                                                       $additional
     * @return array
     */
    public function get($popup, array $additional = []): array
    {
        $config = [
            'display_popup'           => $popup->getDisplayPopup(),
            'delay_time'              => $popup->getDelayTime(),
            'mobile_leave_delay_time' => $popup->getMobileLeaveDelayTime(),
            'page_scroll'             => (int) $popup->getData('page_scroll'),
            'css_selector'            => $popup->getData('css_selector'),
            'success_url'             => $this->getPopupSuccessPage->execute($popup),
            'cookie_time_frame'       => (int) $popup->getOffsetForCookieTimeFrame(),
            'id'                      => (int) $popup->getId(),
            'current_device'          => $this->space->getDevice(),
        ];

        return array_merge($config, $additional);
    }

    /**
     * @param \Plumrocket\Newsletterpopup\Model\Popup|\Plumrocket\Newsletterpopup\Api\Data\PopupInterface $popup
     * @param array                                                                                       $additional
     * @return string
     */
    public function getJson($popup, array $additional = []): string
    {
        return $this->serializer->serialize($this->get($popup, $additional));
    }
}
