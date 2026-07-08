<?php
namespace Digidirect\AbstractEntity\Model\Rest;

use Digidirect\AbstractEntity\Api\Rest\AbstractEntityRestInterface;
use Digidirect\AbstractEntity\Model\AbstractEntityRepository;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class AbstractEntityRest
 * @package Digidirect\AbstractEntity\Model\Rest
 */
class AbstractEntityRest implements AbstractEntityRestInterface
{
    /** @var AbstractEntityRepository */
    protected $aeRepository;

    /** @var Json  */
    protected $jsonHelper;

    const ATTRIBUTES_PARAM_SEPARATOR = ',';

    /**
     * AbstractEntityRest constructor.
     * @param AbstractEntityRepository $abstractEntityRepository
     * @param Json $jsonHelper
     */
    public function __construct(
        AbstractEntityRepository $abstractEntityRepository,
        Json $jsonHelper
    ) {
        $this->aeRepository = $abstractEntityRepository;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Retrieve AbstractEntity
     * @param string $id
     * @param string|null $attributes
     * @return string
     */
    public function getById($id, $attributes = null)
    {
        try {
            $aeEntity = $this->aeRepository->getById($id);
        } catch (\Throwable $e) {
            return $this->jsonHelper->serialize(['error' => $this->getErrorMessage($id)]);
        }

        if (!$aeEntity->isVisibleOnFrontend()) {
            return $this->jsonHelper->serialize(
                ['error' => $this->getErrorMessage($aeEntity->getId())]
            );
        }

        if (empty($attributes)) {
            return $this->jsonHelper->serialize($aeEntity->getData());
        }

        $attributes = explode(static::ATTRIBUTES_PARAM_SEPARATOR, $attributes);
        $data = $aeEntity->getData();
        foreach ($data as $key => $value) {
            if (!in_array($key, $attributes)) {
                $aeEntity->unsetData($key);
            }
        }
        return $this->jsonHelper->serialize($aeEntity->getData());
    }

    /**
     * @param int $id
     * @return \Magento\Framework\Phrase
     */
    public function getErrorMessage($id)
    {
        return __('Abstract Entity with id %1 does not exist.', $id);
    }
}
