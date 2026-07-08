<?php

namespace Digidirect\Utilities\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Helper\Context;

/**
 * @since 1.16.3
 * Ability to work with variables/widgets in given html content from wysiwyg
 */
class Cms extends AbstractHelper
{
    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * Cms constructor.
     * @param Context $context
     * @param FilterProvider $filterProvider
     */
    public function __construct(
        Context $context,
        FilterProvider $filterProvider
    ) {
        parent::__construct($context);
        $this->filterProvider = $filterProvider;
    }


    /**
     * @param string $value
     * @param bool $catchException Introduced for production
     * @return string
     * @throws \Throwable
     */
    public function getCmsFilterContent($value = '', $catchException = false)
    {
        try {
            $html = $this->filterProvider->getPageFilter()->filter($value);
        } catch (\Throwable $exception) {
            if ($catchException) {
                throw $exception;
            }

            $html = $value;
        }
        return $html;
    }
}
