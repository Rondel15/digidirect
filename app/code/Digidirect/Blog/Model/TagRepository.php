<?php

namespace Digidirect\Blog\Model;

use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Api\Data\TagInterface;
use Digidirect\Blog\Api\TagRepositoryInterface;
use Digidirect\Blog\Model\ResourceModel\Tag;
use Digidirect\Blog\Model\TagFactory;
use Digidirect\Blog\Model\ResourceModel\Tag\CollectionFactory;
use Magento\Store\Model\Store;
use Digidirect\Blog\Sql\TagSave;
use Magento\Framework\App\ObjectManager;
use Digidirect\Blog\Model\CurrentStoreFetcher;
use Digidirect\Blog\Api\PostRepositoryInterface;
use Digidirect\Blog\Model\Config\Provider\Status;

class TagRepository implements TagRepositoryInterface
{
    /**
     * @var Post
     */
    protected $resourceModel;

    /**
     * @var TagFactory
     */
    protected $modelFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Digidirect\Blog\Model\Tag[]
     */
    protected $tagByName = [];

    /**
     * @var array
     */
    protected $tagsByPostId = [];

    /**
     * @var TagSave
     */
    protected $tagSave;

    /**
     * @var CurrentStoreFetcher
     */
    protected $currentStoreFetcher;

    /**
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * TagRepository constructor.
     * @param Tag $resourceModel
     * @param \Digidirect\Blog\Model\TagFactory $modelFactory
     * @param CollectionFactory $collectionFactory
     * @param TagSave|null $tagSave
     * @param \Digidirect\Blog\Model\CurrentStoreFetcher|null $currentStoreFetcher
     * @param PostRepositoryInterface|null $postRepository
     */
    public function __construct(
        Tag $resourceModel,
        TagFactory $modelFactory,
        CollectionFactory $collectionFactory,
        TagSave $tagSave = null,
        CurrentStoreFetcher $currentStoreFetcher = null,
        PostRepositoryInterface $postRepository = null
    ) {
        $this->resourceModel = $resourceModel;
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
        $this->tagSave = $tagSave ?:
            ObjectManager::getInstance()->get(TagSave::class);
        $this->currentStoreFetcher = $currentStoreFetcher ?:
            ObjectManager::getInstance()->get(CurrentStoreFetcher::class);
        $this->postRepository = $postRepository ?: ObjectManager::getInstance()->get(PostRepositoryInterface::class);
    }

    /**
     * @param string $tagName
     * @return \Digidirect\Blog\Model\Tag
     */
    public function getByName($tagName)
    {
        if (!isset($this->tagByName[$tagName])) {
            $item = $this->modelFactory->create();
            $this->resourceModel->load($item, $tagName, TagInterface::FILED_NAME);
            $this->tagByName[$tagName] = $item;
        }
        return $this->tagByName[$tagName];
    }

    /**
     * @param int $storeId
     * @return Tag\Collection
     */
    public function getRandomTags($storeId)
    {
        $postAvailable = $this->postRepository->getPostList(Status::STATUS_ENABLED, $storeId);
        $tags = [];
        foreach ($postAvailable as $post) {
            if ($post->getTags()) {
                $tags = array_merge($tags, $post->getTags());
            }
        }

        /** @var Tag\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->getSelect()
            ->where('main_table.name in (?)', array_unique($tags))
            ->order((new \Zend_Db_Expr('RAND()')));
        return $collection;
    }

    /**
     * @param int $postId
     * @return Tag\Collection
     */
    public function getTagsByPostId($postId)
    {
        if (!isset($this->tagsByPostId[$postId])) {
            $storeId = $this->currentStoreFetcher->getCurrentStoreId();
            if (!$this->tagSave->hasStoreViewContent($postId, $storeId)) {
                $storeId = Store::DEFAULT_STORE_ID;
            }

            $collection = $this->collectionFactory->create();
            $collection->getSelect()->joinInner(
                ['rel' => Tag::TAG_POST_RELATION_TABLE],
                'main_table.entity_id = rel.tag_id'
            );

            $collection->getSelect()
                ->where('rel.post_id = ?', (int)$postId)
            ->where('rel.store_id = ?', (int)$storeId);
            $this->tagsByPostId[$postId] = $collection;
        }
        return $this->tagsByPostId[$postId];
    }
}
