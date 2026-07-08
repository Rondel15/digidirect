<?php
namespace Digidirect\AddressVerification\Model\CountryAddress\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Attribute
 * @package Digidirect\AddressVerification\Model\CountryAddress\Source
 */
class Attribute implements ArrayInterface
{
    /**
     * @var array
     */
    protected $providerList;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * CommentType constructor.
     * @param array $providerList
     */
    public function __construct(array $providerList = [])
    {
        $this->providerList = $providerList;
        $this->prepareOptions();
    }

    /**
     * @return void
     */
    protected function prepareOptions()
    {
        foreach ($this->providerList as $provider) {
            if ($provider instanceof SourceProviderInterface) {
                $this->options = array_merge($this->options, $provider->getOptions());
            }
        }
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }

    /**
     * @param string|array $code
     * @return mixed|string
     */
    public function getNameByCode($code)
    {
        $options = $this->toArray();
        if (is_array($code)) {
            return array_intersect_key($options, array_flip($code));
        }
        return isset($options[$code]) ? $options[$code] : '';
    }
}
