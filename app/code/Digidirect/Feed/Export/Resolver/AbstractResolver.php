<?php

namespace Digidirect\Feed\Export\Resolver;

use Digidirect\Feed\Export\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem;

abstract class AbstractResolver
{
    /**
     * Export Context
     *
     * @var Context
     */
    protected $context;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * AbstractResolver constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param PoolFactory $poolFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        PoolFactory $poolFactory
    ) {
        $this->context = $context;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->poolFactory = $poolFactory;
    }

    /**
     * List of allowed attributes
     *
     * @return array
     */
    abstract public function getAttributes();

    /**
     * General resolver
     *
     * @param object $object
     * @param string $key
     * @param []     $args
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function resolve($object, $key, $args = [])
    {
        $this->context->setCurrentObject($object);

        $pool = $this->poolFactory->create();

        $resolver = $pool->findResolver($object);

        if ($resolver && !($resolver instanceof $this)) {
            return $resolver->resolve($object, $key, $args);
        } elseif ($resolver instanceof $this && !$key) {
            return $resolver->toString($object);
        } else {
            $exploded = explode(':', $key);

            $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $exploded[0])));
            for ($i = 1; $i < count($exploded); $i++) {
                $args[] = $exploded[$i];
            }

            if (method_exists($this, 'prepareObject')) {
                $object = $this->{'prepareObject'}($object);
            }

            if (method_exists($this, $method)) {
                return $this->{$method}($object, $args);
            }

            if (method_exists($this, 'getData')) {
                return $this->getData($object, $key);
            }

            if (method_exists($object, $method)) {
                return $object->{$method}();
            }

            if (method_exists($object, 'getData')) {
                return $object->getData($exploded[0]);
            }
        }

        return false;
    }

    /**
     * Return string value of object
     *
     * @param object|array|string $value
     * @param string $key
     * @return string
     */
    public function toString($value, $key = null)
    {
        if (!$key && is_object($value)) {
            return get_class($value);
        }

        if (is_array($value)) {
            if (preg_match('/.category.path$/', $key)) {
                return implode(' > ', $value);
            }

            # is multi-dimension array
            if (isset($value[0]) && is_array($value[0])) {
                return print_r($value, true);
            } else {
                return implode(', ', $value);
            }
        }

        return $value;
    }

    /**
     * Feed model
     *
     * @return \Digidirect\Feed\Model\Feed
     */
    public function getFeed()
    {
        return $this->context->getFeed();
    }
}
