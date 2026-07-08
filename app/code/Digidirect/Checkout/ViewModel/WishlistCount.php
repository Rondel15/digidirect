<?php
namespace Digidirect\Checkout\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Customer\Model\Session;

class WishlistCount implements ArgumentInterface
{
    protected $wishlistFactory;
    protected $customerSession;

    public function __construct(
        WishlistFactory $wishlistFactory,
        Session $customerSession
    ) {
        $this->wishlistFactory = $wishlistFactory;
        $this->customerSession = $customerSession;
    }

    public function getCount(): int
    {
        if (!$this->customerSession->isLoggedIn()) {
            return 0;
        }

        $wishlist = $this->wishlistFactory
            ->create()
            ->loadByCustomerId($this->customerSession->getCustomerId(), true);

        return (int) $wishlist->getItemsCount();
    }
}
