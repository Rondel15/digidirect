<?php
namespace Digidirect\LayeredNavigation\Api;

interface FilterSettingRepositoryInterface
{
    /**
     * Load Filter Setting data by given Identity
     *
     * @param int $filterSettingId
     * @return Data\FilterSettingInterface
     */
    public function getById($filterSettingId);

    /**
     * Load Filter Setting data by given filter code
     *
     * @param string $filterCode
     * @return Data\FilterSettingInterface
     */
    public function loadByFilterCode($filterCode);

    /**
     * @param Data\FilterSettingInterface $filterSetting
     * @return Data\FilterSettingInterface
     */
    public function save(Data\FilterSettingInterface $filterSetting);

    /**
     * @param Data\FilterSettingInterface $filterSetting
     * @return bool
     */
    public function delete(Data\FilterSettingInterface $filterSetting);

    /**
     * Delete Filter Setting by given Identity
     *
     * @param int $filterSettingId
     * @return bool
     */
    public function deleteById($filterSettingId);
}
