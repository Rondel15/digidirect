<?php
namespace Digidirect\Blog\Block\Post\Comments;

use Digidirect\Blog\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class Facebook
 */
class Facebook extends AbstractCommentType
{
    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * Facebook constructor.
     * @param Template\Context $context
     * @param Data $dataHelper
     * @param Registry $registry
     * @param ResolverInterface $localeResolver
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $dataHelper,
        Registry $registry,
        ResolverInterface $localeResolver,
        array $data
    ) {
        parent::__construct($context, $dataHelper, $registry, $data);
        $this->localeResolver = $localeResolver;
    }

    /**
     * @return string
     */
    public function getCommentsNumber()
    {
        return $this->dataHelper->getCommentSettingsConfig('no_of_comments');
    }

    /**
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->dataHelper->getCommentSettingsConfig('fb_app_id');
    }
}
