<?php

namespace Digidirect\Blog\Registry;

use Digidirect\Blog\Api\Data\PostInterface;
use Magento\Framework\Registry;

/**
 * @since 2.0.0
 */
class CurrentPostItem
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * CurrentPostItem constructor.
     *
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @return PostInterface|null
     */
    public function get()
    {
        return $this->registry->registry(PostInterface::CURRENT_ITEM);
    }

    /**
     * @param PostInterface $post
     * @return CurrentPostItem
     */
    public function set(PostInterface $post): CurrentPostItem
    {
        $this->registry->register(PostInterface::CURRENT_ITEM, $post);
        return $this;
    }
}
