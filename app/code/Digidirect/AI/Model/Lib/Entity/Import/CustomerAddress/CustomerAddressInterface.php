<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\CustomerAddress;

use Digidirect\AI\Model\Lib\Entity\Import\ImportInterface;

/**
 * Interface CustomerAddressInterface
 *
 */
interface CustomerAddressInterface extends ImportInterface
{
    /**
     * @param string $customerEmail
     * @param string $websiteCode
     * @param int|string $addressId
     * @return mixed
     */
    public function deleteCustomerAddress($customerEmail, $websiteCode, $addressId);

    /**
     * @param array $addressIds
     * @return mixed
     */
    public function deleteBunch(array $addressIds);
}
