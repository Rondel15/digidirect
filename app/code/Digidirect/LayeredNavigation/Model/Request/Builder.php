<?php
namespace Digidirect\LayeredNavigation\Model\Request;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\RequestInterface;

class Builder extends \Magento\Framework\Search\Request\Builder
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * @var array
     */
    protected $removablePlaceholders = [];

    /**
     * Builder constructor.
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Search\Request\Config $config
     * @param \Magento\Framework\Search\Request\Binder $binder
     * @param \Magento\Framework\Search\Request\Cleaner $cleaner
     * @param \Magento\Framework\App\Request\Http $http
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \Magento\Framework\Search\Request\Config $config,
        \Magento\Framework\Search\Request\Binder $binder,
        \Magento\Framework\Search\Request\Cleaner $cleaner,
        \Magento\Framework\App\Request\Http $http
    ) {
        parent::__construct($objectManager, $config, $binder, $cleaner);
        $this->httpRequest = $http;
    }

    /**
     * autoSetRequestName
     * @return void
     */
    public function autoSetRequestName()
    {
        $module = $this->httpRequest->getControllerModule();
        switch ($module) {
            case 'Magento_CatalogSearch':
                $requestName = 'quick_search_container';
                break;
            default:
                $requestName = 'catalog_view_container';
        }

        $this->setRequestName($requestName);
    }

    /**
     * @param string $placeholder
     * @param mixed $value
     * @return $this
     */
    public function bind($placeholder, $value)
    {
        $this->removablePlaceholders[$placeholder] = $value;
        return $this;
    }

    /**
     * @param string $placeholder
     * @return $this
     */
    public function removePlaceholder($placeholder)
    {
        if (array_key_exists($placeholder, $this->removablePlaceholders)) {
            unset($this->removablePlaceholders[$placeholder]);
        }

        return $this;
    }

    /**
     * @param string $placeholder
     * @return mixed
     */
    public function hasPlaceholder($placeholder)
    {
        return array_key_exists($placeholder, $this->removablePlaceholders);
    }

    /**
     * Create request object
     *
     * @return RequestInterface
     */
    public function create()
    {
        $this->commitCancelablePlaceholders();
        return parent::create();
    }

    /**
     * commitCancelablePlaceholders
     * @return void
     */
    protected function commitCancelablePlaceholders()
    {
        foreach ($this->removablePlaceholders as $key => $value) {
            parent::bind($key, $value);
        }
    }

    /**
     * Get all placeholders
     *
     * @return array
     */
    public function getAllPlaceholders()
    {
        return $this->removablePlaceholders;
    }
}
