<?php
namespace Digidirect\Blog\Model\Config;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class AbstractSource
 */
class AbstractSource implements ArrayInterface
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
}
