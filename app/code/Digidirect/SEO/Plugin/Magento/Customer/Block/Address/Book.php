<?php
namespace Digidirect\SEO\Plugin\Magento\Customer\Block\Address;

use Digidirect\SEO\Helper\TrailingSlash;
use Magento\Customer\Block\Address\Book as AddressBook;

/**
 * Class Book
 *
 * As default magento uses for address book getUrl() ?>id concatenation we don't need to remove trailing slash
 */
class Book
{
    /**
     * @var TrailingSlash
     */
    protected $helper;

    /**
     * Book constructor.
     *
     * @param TrailingSlash $trailingSlashHelper
     */
    public function __construct(TrailingSlash $trailingSlashHelper)
    {
        $this->helper = $trailingSlashHelper;
    }

    /**
     * @param AddressBook $book
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetDeleteUrl(AddressBook $book, $result)
    {
        if (!$this->helper->isTrailingSlashEnabled()) {
            return $this->helper->addTrailingSlash($result);
        }
        return $result;
    }
}
