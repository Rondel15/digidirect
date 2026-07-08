<?php
namespace Digidirect\ProductOverlay\Model\Rule;

use Digidirect\ProductOverlay\Model\Overlays;
use Magento\Customer\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Stock
 *
 * @package Digidirect\ProductOverlay\Model\Rule
 *
 */
class CustomerGroup implements ProcessorInterface
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * CustomerGroup constructor.
     *
     * @param Session $session
     * @param Json $serialize
     */
    public function __construct(Session $session, Json $serialize)
    {
        $this->customerSession = $session;
        $this->serializer = $serialize;
    }

    /**
     * @param Overlays $overlay
     * @return bool
     */
    public function isApplicable(Overlays $overlay)
    {
        $customerGroupIds = $overlay->getCustomerGroupIds();
        if (!$customerGroupIds) {
            return false;
        }

        $customerGroupIdsAsArray = $this->serializer->unserialize($customerGroupIds);

        if (is_array($customerGroupIdsAsArray) &&
            !in_array($this->customerSession->getCustomerGroupId(), $customerGroupIdsAsArray)
        ) {
            return false;
        }

        return true;
    }
}
