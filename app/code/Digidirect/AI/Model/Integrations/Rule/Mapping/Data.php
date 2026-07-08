<?php
namespace Digidirect\AI\Model\Integrations\Rule\Mapping;

use Digidirect\AI\Api\Data\MapperDataInterface;
use Magento\Framework\Model\AbstractModel;

class Data extends AbstractModel implements MapperDataInterface
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\AI\Model\ResourceModel\Integrations\Rule\Mapping');
    }

    /**
     * Get mapper code
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->getData(self::MAPPER_CODE);
    }

    /**
     * Set mapper code
     *
     * @param string $code
     * @return MapperDataInterface
     */
    public function setCode($code)
    {
        return $this->setData(self::MAPPER_CODE, $code);
    }

    /**
     * Get data
     *
     * @return string|null
     */
    public function getMapperData()
    {
        $data = $this->getData(self::DATA);
        if ($data) {
            return unserialize($data);
        }
        return [];
    }

    /**
     * Set data
     *
     * @param array $data
     * @return MapperDataInterface
     */
    public function setMapperData($data = [])
    {
        if (!is_array($data)) {
            $data = [];
        }

        if (isset($data['free'])) {
            unset($data['free']);
        }

        return $this->setData(self::DATA, serialize($data));
    }
}
