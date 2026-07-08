<?php
namespace Digidirect\Utilities\Plugin\Magento\Widget\Model\ResourceModel\Widget;

use Magento\Widget\Model\ResourceModel\Widget\Instance as MagentoResourceModelInstance;
use Magento\Widget\Model\Widget\Instance as MagentoWidgetInstance;

/**
 * Class Instance
 * Fixes 404 page handle
 */
class Instance
{
    const PAGE_GROUP_PAGE = 'page';
    const PAGE_GROUP_PAGES_KEY = 'pages';
    const LAYOUT_HANDLE_KEY = 'layout_handle';

    /**
     * @var array
     */
    protected $handlesToCorrect;

    /**
     * Instance constructor.
     *
     * @param array $handlesToCorrect
     */
    public function __construct(array $handlesToCorrect = [])
    {
        $this->handlesToCorrect = $handlesToCorrect;
    }

    /**
     * Fix no-route handle
     *
     * @param MagentoResourceModelInstance $instance
     * @param \Closure $closure
     * @param MagentoWidgetInstance $widgetInstance
     * @return mixed
     */
    public function aroundSave(
        MagentoResourceModelInstance $instance,
        \Closure $closure,
        MagentoWidgetInstance $widgetInstance
    ) {
        $pageGroups = $widgetInstance->getData('page_groups');

        if (!empty($pageGroups)) {
            foreach ($pageGroups as $key => $pageGroup) {
                if ($this->needUpdateHandle($pageGroup)) {
                    $this->updateHandle($pageGroup);
                }
                $pageGroups[$key] = $pageGroup;
            }
            $widgetInstance->setData('page_groups', $pageGroups);
        }

        return $closure($widgetInstance);
    }

    /**
     * @param array $pageGroup
     * @return void
     */
    protected function updateHandle(&$pageGroup)
    {
        $currentHandle = $pageGroup[self::PAGE_GROUP_PAGES_KEY][self::LAYOUT_HANDLE_KEY] ?? null;
        $newHandle = $this->handlesToCorrect[$currentHandle] ?? null;
        if ($newHandle) {
            $pageGroup[self::PAGE_GROUP_PAGES_KEY][self::LAYOUT_HANDLE_KEY] = $newHandle;
        }
    }

    /**
     * @param array $pageGroup
     * @return bool
     */
    protected function needUpdateHandle(array $pageGroup = [])
    {
        $pageGroupData = $this->getValue('page_group', $pageGroup);
        if ($pageGroupData == self::PAGE_GROUP_PAGES_KEY) {
            $layoutHandle = $pageGroup[self::PAGE_GROUP_PAGES_KEY][self::LAYOUT_HANDLE_KEY] ?? null;
            return isset($this->handlesToCorrect[$layoutHandle]);
        }

        return false;
    }

    /**
     * Get Value By Key
     *
     * @param array $array
     * @param string $key
     * @return mixed|null
     */
    protected function getValue($key, array $array = [])
    {
        return $array[$key] ?? null;
    }

    /**
     * @param MagentoResourceModelInstance $instance
     * @param \Closure $closure
     * @param MagentoWidgetInstance $widgetInstance
     * @param string|int $value
     * @param null $field
     * @return mixed
     */
    public function aroundLoad(
        MagentoResourceModelInstance $instance,
        \Closure $closure,
        MagentoWidgetInstance $widgetInstance,
        $value,
        $field = null
    ) {
        $result = $closure($widgetInstance, $value, $field);

        $pageGroups = $widgetInstance->getData('page_groups');

        if (!empty($pageGroups)) {
            foreach ($pageGroups as $key => $pageGroup) {
                if ($this->needToRevertHandle($pageGroup)) {
                    $this->revertHandle($pageGroup);
                }
                $pageGroups[$key] = $pageGroup;
            }
            $widgetInstance->setData('page_groups', $pageGroups);
        }
        return $result;
    }

    /**
     * @param array $pageGroup
     * @return bool|mixed
     */
    protected function needToRevertHandle(array $pageGroup = [])
    {
        $pageGroupData = $this->getValue('page_group', $pageGroup);
        if ($pageGroupData == self::PAGE_GROUP_PAGES_KEY) {
            $layoutHandle = $pageGroup[self::LAYOUT_HANDLE_KEY] ?? null;
            return in_array($layoutHandle, $this->handlesToCorrect);
        }

        return false;
    }

    /**
     * @param [] $pageGroup
     * @return void
     */
    protected function revertHandle(&$pageGroup)
    {
        $currentHandle = $pageGroup[self::LAYOUT_HANDLE_KEY] ?? null;
        $key = array_search($currentHandle, $this->handlesToCorrect);
        if ($key) {
            $pageGroup[self::LAYOUT_HANDLE_KEY] = $key;
        }
    }
}
