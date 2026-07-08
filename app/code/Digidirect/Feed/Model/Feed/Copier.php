<?php

namespace Digidirect\Feed\Model\Feed;

use Digidirect\Feed\Model\Feed;
use Digidirect\Feed\Model\FeedFactory;
use Digidirect\Feed\Model\FeedRepository;

class Copier
{
    /**
     * Feed Factory
     *
     * @var FeedFactory
     */
    protected $feedFactory;

    /**
     * @var FeedRepository
     */
    protected $feedRepository;

    /**
     * Copier constructor.
     * @param FeedFactory $feedFactory
     * @param FeedRepository $feedRepository
     */
    public function __construct(
        FeedFactory $feedFactory,
        FeedRepository $feedRepository
    ) {
        $this->feedFactory = $feedFactory;
        $this->feedRepository = $feedRepository;
    }

    /**
     * Create new copy of feed
     *
     * @param Feed $feed
     * @return Feed
     */
    public function copy(Feed $feed)
    {
        $copy = $this->feedFactory->create()
            ->setData($feed->getData())
            ->setId(null)
            ->setCreatedAt(null)
            ->setUpdatedAt(null)
            ->setGeneratedAt(null)
            ->setGeneratedCnt(null)
            ->setGeneratedTime(null)
            ->setUploadedAt(null)
            ->setName($feed->getName() . ' copy')
            ->setFilename($feed->getData('filename') . '_copy')
            ->setRuleIds($feed->getRuleIds());

        $this->feedRepository->save($copy);

        return $copy;
    }
}
