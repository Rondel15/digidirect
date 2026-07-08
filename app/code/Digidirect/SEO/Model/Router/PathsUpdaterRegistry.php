<?php

namespace Digidirect\SEO\Model\Router;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class PathsUpdaterRegistry
 *
 * @package Digidirect\SEO\Model\Router
 */
class PathsUpdaterRegistry
{
    /**
     * No route handlers instances
     *
     * @var PathsUpdaterInterface[]
     */
    protected $updaters;

    /**
     * @var array
     */
    protected $updaterClassesList;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array                  $updaterClassesList
     */
    public function __construct(ObjectManagerInterface $objectManager, array $updaterClassesList = [])
    {
        $this->updaterClassesList = $updaterClassesList;
        $this->objectManager = $objectManager;
    }

    /**
     * @return PathsUpdaterInterface[]
     */
    public function getUpdaters()
    {
        if ($this->updaters) {
            return $this->updaters;
        }
        $sortedUpdatersList = [];
        foreach ($this->updaterClassesList as $updaterInfo) {
            $updaterInfo['enabled'] = $updaterInfo['enabled'] ?? true;
            if (!$updaterInfo['enabled']) {
                continue;
            }
            if (isset($updaterInfo['class']) && isset($updaterInfo['sortOrder'])) {
                $sortedUpdatersList[$updaterInfo['class']] = $updaterInfo['sortOrder'];
            }
        }

        asort($sortedUpdatersList);

        $this->updaters = [];
        //creating handlers
        foreach (array_keys($sortedUpdatersList) as $updaterInstance) {
            $this->updaters[] = $this->objectManager->create($updaterInstance);
        }

        return $this->updaters;
    }
}
