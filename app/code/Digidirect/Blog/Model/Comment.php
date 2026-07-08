<?php

namespace Digidirect\Blog\Model;

use Digidirect\Blog\Api\Data\CommentInterface;
use Digidirect\Blog\Api\PostRepositoryInterface;
use Digidirect\Blog\Helper\Data;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Translate\Inline\StateInterface as InlineTranslateStateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Escaper;
use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Comment extends \Magento\Framework\Model\AbstractModel implements CommentInterface, IdentityInterface
{
    const ADMIN_EMAIL_TEMPLATE = 'digidirect_blog_comments_email_template';
    const CACHE_PREFIX = 'blog_comment';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_blog_comment';

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * Inline Translation
     *
     * @var InlineTranslateStateInterface
     */
    protected $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * @var IdentitiesGenerator
     */
    protected $identitiesGenerator;

    /**
     * Comment constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Post $resource
     * @param ResourceModel\Post\Collection $resourceCollection
     * @param Data $dataHelper
     * @param InlineTranslateStateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param Escaper $escaper
     * @param PostRepositoryInterface $postRepository
     * @param IdentitiesGenerator $identitiesGenerator
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Digidirect\Blog\Model\ResourceModel\Post $resource,
        \Digidirect\Blog\Model\ResourceModel\Post\Collection $resourceCollection,
        Data $dataHelper,
        InlineTranslateStateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        Escaper $escaper,
        PostRepositoryInterface $postRepository,
        IdentitiesGenerator $identitiesGenerator
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->dataHelper = $dataHelper;
        $this->transportBuilder = $transportBuilder;
        $this->escaper = $escaper;
        $this->inlineTranslation = $inlineTranslation;
        $this->postRepository = $postRepository;
        $this->identitiesGenerator = $identitiesGenerator;
    }

    /**
     * @return void
     */
    public function sendAdminEmail()
    {
        $notificationEnabled = $this->dataHelper->getCommentSettingsConfig('emailoncomment');
        $adminEmail = $this->dataHelper->getCommentSettingsConfig('admin_email');
        if ($notificationEnabled && !empty($adminEmail)) {
            $post = $this->postRepository->getById($this->getPostId());
            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->escaper->escapeHtml($this->getSenderName()),
                'email' => $this->escaper->escapeHtml($this->getSenderEmail()),
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier(self::ADMIN_EMAIL_TEMPLATE)
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(
                    [
                        'postTitle' => $post->getTitle(),
                        'authorName' => $this->getSenderName(),
                        'authorEmail' => $this->getSenderEmail(),
                        'comment' => $this->getComment(),
                    ]
                )
                ->setFrom($sender)
                ->addTo($adminEmail)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
        }
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return $this->identitiesGenerator->getIdentities($this, self::CACHE_PREFIX);
    }
}
