<?php

namespace Digidirect\AbstractGiftCard\Api;

interface AbstractGiftCardEntityRepositoryInterface
{

    /**
     * Loads a specified abstract giftcard entity.
     *
     * @param int $id The abstract giftcard entity ID.
     * @return \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface abstract giftcard entity interface.
     */
    public function get($id);

    /**
     * Performs persist operations for a specified abstract giftcard entity.
     *
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $entity The abstract giftcard entity ID.
     * @return \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface abstract giftcard entity interface.
     */
    public function save(AbstractGiftCardEntityInterface $entity);

    /**
     * Loads a specified abstract giftcard entity.
     *
     * @param string $serviceCode giftcard service code
     * @param string $giftCardCode code of specified giftcard
     * @return mixed
     */
    public function getGiftCardDataByServiceAndCode($serviceCode, $giftCardCode);

    /**
     * @param \Magento\GiftCardAccount\Model\Giftcardaccount $giftcardaccount
     * @return AbstractGiftCardEntityInterface
     * @throws NoSuchEntityException
     */
    public function loadByGiftCardAccount(\Magento\GiftCardAccount\Model\Giftcardaccount $giftcardaccount);

    /**
     * @param \Magento\Framework\DataObject $entityOrderData
     * @return \Digidirect\AbstractGiftCard\Model\ResourceModel\AbstractGiftCardEntity
     */
    public function saveEntityOrderData(\Magento\Framework\DataObject $entityOrderData);
}
