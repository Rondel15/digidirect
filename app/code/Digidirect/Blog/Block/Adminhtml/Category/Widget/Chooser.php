<?php

namespace Digidirect\Blog\Block\Adminhtml\Category\Widget;

use Digidirect\Blog\Model\Config\Provider\Status;
use Digidirect\Blog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Chooser extends Extended
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * Store selected categories Ids
     * Used in initial setting selected categories
     *
     * @var array
     */
    protected $_selectedCategories = [];

    /**
     * Store hidden categories ids field id
     *
     * @var string
     */
    protected $_elementValueId = '';

    /**
     * Category resource collection factory
     *
     * @var CollectionFactory
     */
    protected $_categoryColFactory = null;

    /**
     * @var Status
     */
    protected $_categoryStatus;

    /**
     * Chooser constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param Status $status
     * @param CollectionFactory $categoryColFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        Status $status,
        CollectionFactory $categoryColFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_categoryStatus = $status;
        $this->_categoryColFactory = $categoryColFactory;
        $this->_elementFactory = $elementFactory;
    }

    /**
     * Block construction, prepare grid params
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('categoryGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('category_filter');
        $this->setDefaultFilter(['in_categories' => 1]);
    }

    /**
     * Instantiate and prepare collection
     * Set categories' positions of saved categories
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_categoryColFactory->create();
        $this->setCollection($collection);
        parent::_prepareCollection();

        foreach ($this->getCollection() as $item) {
            foreach ($this->getSelectedCategories() as $pos => $category) {
                if ($category == $item->getCategoryId()) {
                    $item->setPosition($pos + 1);
                }
            }
        }
        return $this;
    }

    /**
     * Define grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_categories',
            [
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select',
                'type' => 'checkbox',
                'name' => 'in_categories',
                'values' => $this->getSelectedCategories(),
                'index' => 'entity_id'
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'title_category',
            [
                'header' => __('Category'),
                'type' => 'text',
                'index' => 'name',
                'escape' => true,
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'source' => Status::class,
                'options' => $this->_categoryStatus->getOptionArray()
            ]
        );

        $this->addColumnAfter(
            'position',
            [
                'header' => __('Position'),
                'name' => 'position',
                'type' => 'number',
                'validate_class' => 'validate-number',
                'index' => 'position',
                'editable' => true,
                'filter' => false,
                'edit_only' => true,
                'sortable' => false
            ],
            'status'
        );

        return parent::_prepareColumns();
    }
    
    /**
     * Prepare chooser element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $this->_elementValueId = "{$element->getId()}";
        $this->_selectedCategories = explode(',', $element->getValue());

        //Create hidden field that store selected category ids
        $hidden = $this->_elementFactory->create('hidden', ['data' => $element->getData()]);
        $hidden->setId($this->_elementValueId)->setForm($element->getForm());
        $hiddenHtml = $hidden->getElementHtml();

        $element->setValue('')->setValueClass('value2');
        $element->setData('css_class', 'grid-chooser');
        $element->setData('after_element_html', $hiddenHtml . $this->toHtml());
        $element->setData('no_wrap_as_addon', true);

        return $element;
    }
    
    /**
     * Grid row init js callback
     *
     * @return string
     */
    public function getRowInitCallback()
    {
        return '
        function(grid, row){
            if(!grid.selCategoryIds){
                grid.selCategoryIds = {};
                if($(\'' .
            $this->_elementValueId .
            '\').value != \'\'){
                    var elementValues = $(\'' .
            $this->_elementValueId .
            '\').value.split(\',\');
                    for(var i = 0; i < elementValues.length; i++){
                        grid.selCategoryIds[elementValues[i]] = i+1;
                    }
                }
                grid.reloadParams = {};
                grid.reloadParams[\'selected_categories[]\'] = Object.keys(grid.selCategoryIds);
            }
            var inputs      = Element.select($(row), \'input\');
            var checkbox    = inputs[0];
            var position    = inputs[1];
            var catsNum  = grid.selCategoryIds.length;
            var categoryId    = checkbox.value;

            inputs[1].checkboxElement = checkbox;

            var indexOf = grid.selCategoryIds[categoryId];
            if(indexOf >= 0){
                checkbox.checked = true;
                if (!position.value) {
                    position.value = indexOf;
                }
            }

            Event.observe(position,\'change\', function(){
                var checkb = Element.select($(row), \'input\')[0];
                if(checkb.checked){
                    grid.selCategoryIds[checkb.value] = this.value;
                    var idsclone = Object.clone(grid.selCategoryIds);
                    var bans = Object.keys(grid.selCategoryIds);
                    var pos = Object.values(grid.selCategoryIds).sort(sortNumeric);
                    var categories = [];
                    var k = 0;

                    for(var j = 0; j < pos.length; j++){
                        for(var i = 0; i < bans.length; i++){
                            if(idsclone[bans[i]] == pos[j]){
                                categories[k] = bans[i];
                                k++;
                                delete(idsclone[bans[i]]);
                                break;
                            }
                        }
                    }
                    $(\'' .
            $this->_elementValueId .
            '\').value = categories.join(\',\');
                }
            });
        }
        ';
    }
    
    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        return '
            function (grid, event) {
                if(!grid.selCategoryIds){
                    grid.selCategoryIds = {};
                }

                var trElement   = Event.findElement(event, "tr");
                var isInput     = Event.element(event).tagName == \'INPUT\';
                var inputs      = Element.select(trElement, \'input\');
                var checkbox    = inputs[0];
                var position    = inputs[1].value || 1;
                var checked     = isInput ? checkbox.checked : !checkbox.checked;
                checkbox.checked = checked;
                var categoryId    = checkbox.value;

                if(checked){
                    grid.selCategoryIds[categoryId] = position;
                }
                else{
                    delete(grid.selCategoryIds[categoryId]);
                }

                var idsclone = Object.clone(grid.selCategoryIds);
                var bans = Object.keys(grid.selCategoryIds);
                var pos = Object.values(grid.selCategoryIds).sort(sortNumeric);
                var categories = [];
                var k = 0;
                for(var j = 0; j < pos.length; j++){
                    for(var i = 0; i < bans.length; i++){
                        if(idsclone[bans[i]] == pos[j]){
                            categories[k] = bans[i];
                            k++;
                            delete(idsclone[bans[i]]);
                            break;
                        }
                    }
                }
                $(\'' .
            $this->_elementValueId .
            '\').value = categories.join(\',\');
                grid.reloadParams = {};
                grid.reloadParams[\'selected_categories[]\'] = categories;
            }
        ';
    }

    /**
     * Checkbox Check JS Callback
     *
     * @return string
     */
    public function getCheckboxCheckCallback()
    {
        return 'function (grid, element, checked) {
                    if(!grid.selCategoryIds){
                        grid.selCategoryIds = {};
                    }
                    var checkbox    = element;

                    checkbox.checked = checked;
                    var categoryId    = checkbox.value;
                    if(categoryId == \'on\'){
                        return;
                    }
                    var trElement   = element.up(\'tr\');
                    var inputs      = Element.select(trElement, \'input\');
                    var position    = inputs[1].value || 1;

                    if(checked){
                        if(Object.keys(grid.selCategoryIds).indexOf(categoryId) < 0){
                            grid.selCategoryIds[categoryId] = position;
                        }
                    }
                    else{
                        delete(grid.selCategoryIds[categoryId]);
                    }

                    var idsclone = Object.clone(grid.selCategoryIds);
                    var bans = Object.keys(grid.selCategoryIds);
                    var pos = Object.values(grid.selCategoryIds).sort(sortNumeric);
                    var categories = [];
                    var k = 0;
                    for(var j = 0; j < pos.length; j++){
                        for(var i = 0; i < bans.length; i++){
                            if(idsclone[bans[i]] == pos[j]){
                                categories[k] = bans[i];
                                k++;
                                delete(idsclone[bans[i]]);
                                break;
                            }
                        }
                    }
                    $(\'' .
            $this->_elementValueId .
            '\').value = categories.join(\',\');
                    grid.reloadParams = {};
                    grid.reloadParams[\'selected_categories[]\'] = categories;
                }';
    }

    /**
     * Adds additional parameter to URL for loading only categories grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'digidirect_blog/category_widget/chooser',
            [
                'category_grid' => true,
                '_current' => true,
                'uniq_id' => $this->getId(),
                'selected_categories' => join(',', $this->getSelectedCategories())
            ]
        );
    }

    /**
     * Setter
     *
     * @param array $selectedCategories
     * @return $this
     */
    public function setSelectedCategories($selectedCategories)
    {
        if (is_string($selectedCategories)) {
            $selectedCategories = explode(',', $selectedCategories);
        }
        $this->_selectedCategories = $selectedCategories;
        return $this;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getSelectedCategories()
    {
        if ($selectedCategories = $this->getRequest()->getParam('selected_categories', $this->_selectedCategories)) {
            $this->setSelectedCategories($selectedCategories);
        }
        return $this->_selectedCategories;
    }

    /**
     * Disable mass action functionality
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Set custom filter for in category flag
     *
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_categories') {
            $categoryIds = $this->getSelectedCategories();
            if (empty($categoryIds)) {
                $categoryIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addCategoryIdsFilter($categoryIds);
            } else {
                if ($categoryIds) {
                    $this->getCollection()->addCategoryIdsFilter($categoryIds, true);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Process column filtration values
     *
     * @param mixed $data
     * @return $this
     */
    protected function _setFilterValues($data)
    {
        foreach ($this->getColumns() as $columnId => $column) {
            if (
                isset($data[$columnId])
                &&
                (is_array($data[$columnId]) && !empty($data[$columnId]) || strlen($data[$columnId]) > 0)
                &&
                $column->getFilter()
            ) {
                $column->getFilter()->setValue($data[$columnId]);
                $this->_addColumnFilterToCollection($column);
            }
        }
        return $this;
    }
}
