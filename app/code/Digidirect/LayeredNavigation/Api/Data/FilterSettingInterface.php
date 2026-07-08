<?php
namespace Digidirect\LayeredNavigation\Api\Data;

interface FilterSettingInterface
{
    /**
     * Setting ID
     */
    const FILTER_SETTING_ID = 'setting_id';

    /**
     * Filter Code
     */
    const FILTER_CODE = 'filter_code';

    /**
     * Display Mode
     */
    const DISPLAY_MODE = 'display_mode';

    /**
     * Is MultiSelect
     */
    const IS_MULTISELECT = 'is_multiselect';

    /**
     * Index Mode
     */
    const INDEX_MODE = 'index_mode';

    /**
     * Follow Mode
     */
    const FOLLOW_MODE = 'follow_mode';

    /**
     * Hide One option
     */
    const HIDE_ONE_OPTION = 'hide_one_option';

    /**
     * Use AND logic
     */
    const USE_AND_LOGIC = 'use_and_logic';

    /**
     * Is show more enabled
     */
    const SHOW_MORE_ENABLED = 'show_more_enabled';

    /**
     * Show more count
     */
    const SHOW_MORE_COUNT = 'show_more_count';

    /**
     * Default show more count
     */
    const DEFAULT_SHOW_MORE_COUNT = 5;

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getDisplayMode();

    /**
     * @return int
     */
    public function getFollowMode();

    /**
     * @return string|null
     */
    public function getFilterCode();

    /**
     * @return int
     */
    public function getHideOneOption();

    /**
     * @return int
     */
    public function getIndexMode();

    /**
     * @return bool|null
     */
    public function isMultiselect();

    /**
     * @return bool|null
     */
    public function useAndLogic();

    /**
     * @return bool|null
     */
    public function isShowMoreEnabled();

    /**
     * @return int|null
     */
    public function getShowMoreCount();

    /**
     * @param int $id
     * @return FilterSettingInterface
     */
    public function setId($id);

    /**
     * @param int $displayMode
     * @return FilterSettingInterface
     */
    public function setDisplayMode($displayMode);

    /**
     * @param int $indexMode
     * @return FilterSettingInterface
     */
    public function setIndexMode($indexMode);

    /**
     * @param int $followMode
     * @return FilterSettingInterface
     */
    public function setFollowMode($followMode);

    /**
     * @param int $hideOneOption
     * @return FilterSettingInterface
     */
    public function setHideOneOption($hideOneOption);

    /**
     * @param bool $isMultiselect
     * @return FilterSettingInterface
     */
    public function setIsMultiselect($isMultiselect);

    /**
     * @param string $filterCode
     * @return FilterSettingInterface
     */
    public function setFilterCode($filterCode);

    /**
     * @param string $useAndLogic
     * @return FilterSettingInterface
     */
    public function setUseAndLogic($useAndLogic);

    /**
     * @param bool $enabled
     * @return FilterSettingInterface
     */
    public function setShowMoreEnabled($enabled);

    /**
     * @param int $count
     * @return FilterSettingInterface
     */
    public function setShowMoreCount($count);
}
