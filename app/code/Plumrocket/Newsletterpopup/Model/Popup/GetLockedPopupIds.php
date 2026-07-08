<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup;

use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Retrieve list of disabled popup by customer or guest
 *
 * @since 4.0.0
 */
class GetLockedPopupIds
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     */
    public function __construct(CookieManagerInterface $cookieManager)
    {
        $this->cookieManager = $cookieManager;
    }

    /**
     * Check which newsletters was disabled.
     *
     * @return int[]
     */
    public function execute(): array
    {
        $ids = [];
        foreach (range(0, 100) as $n) {
            if ($this->cookieManager->getCookie("prnewsletterpopup_disable_popup_$n")) {
                $ids[] = $n;
            }
        }
        return $ids;
    }
}
