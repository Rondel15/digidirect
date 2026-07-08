<?php

namespace Digidirect\Blog\Block\Adminhtml\Post\Widget;

use Digidirect\Blog\Model\Config\Provider\Status;
use Digidirect\Blog\Model\ResourceModel\Post\CollectionFactory;
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
     * Store selected posts Ids
     * Used in initial setting selected posts
     *
     * @var array
     */
    protected $_selectedPosts = [];

    /**
     * Store hidden posts ids field id
     *
     * @var string
     */
    protected $_elementValueId = '';

    /**
     * Post resource collection factory
     *
     * @var CollectionFactory
     */
    protected $_postColFactory = null;

    /**
     * @var Status
     */
    protected $_postStatus;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param Status $status
     * @param CollectionFactory $postColFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        Status $status,
        CollectionFactory $postColFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_postStatus = $status;
        $this->_postColFactory = $postColFactory;
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
        $this->setId('postGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
        $this->setDefaultFilter(['in_posts' => 1]);
    }

    /**
     * Instantiate and prepare collection
     * Set posts' positions of saved posts
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_postColFactory->create();
        $this->setCollection($collection);
        parent::_prepareCollection();

        foreach ($this->getCollection() as $item) {
            foreach ($this->getSelectedPosts() as $pos => $post) {
                if ($post == $item->getPostId()) {
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
            'in_posts',
            [
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select',
                'type' => 'checkbox',
                'name' => 'in_posts',
                'values' => $this->getSelectedPosts(),
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
            'title_post',
            [
                'header' => __('Post'),
                'type' => 'text',
                'index' => 'title',
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
                'options' => $this->_postStatus->getOptionArray()
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
        $this->_selectedPosts = explode(',', $element->getValue());

        //Create hidden field that store selected post ids
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
            if(!grid.selPostsIds){
                grid.selPostsIds = {};
                if($(\'' .
            $this->_elementValueId .
            '\').value != \'\'){
                    var elementValues = $(\'' .
            $this->_elementValueId .
            '\').value.split(\',\');
                    for(var i = 0; i < elementValues.length; i++){
                        grid.selPostsIds[elementValues[i]] = i+1;
                    }
                }
                grid.reloadParams = {};
                grid.reloadParams[\'selected_posts[]\'] = Object.keys(grid.selPostsIds);
            }
            var inputs      = Element.select($(row), \'input\');
            var checkbox    = inputs[0];
            var position    = inputs[1];
            var postsNum  = grid.selPostsIds.length;
            var postId    = checkbox.value;

            inputs[1].checkboxElement = checkbox;

            var indexOf = grid.selPostsIds[postId];
            if(indexOf >= 0){
                checkbox.checked = true;
                if (!position.value) {
                    position.value = indexOf;
                }
            }

            Event.observe(position,\'change\', function(){
                var checkb = Element.select($(row), \'input\')[0];
                if(checkb.checked){
                    grid.selPostsIds[checkb.value] = this.value;
                    var idsclone = Object.clone(grid.selPostsIds);
                    var bans = Object.keys(grid.selPostsIds);
                    var pos = Object.values(grid.selPostsIds).sort(sortNumeric);
                    var posts = [];
                    var k = 0;

                    for(var j = 0; j < pos.length; j++){
                        for(var i = 0; i < bans.length; i++){
                            if(idsclone[bans[i]] == pos[j]){
                                posts[k] = bans[i];
                                k++;
                                delete(idsclone[bans[i]]);
                                break;
                            }
                        }
                    }
                    $(\'' .
            $this->_elementValueId .
            '\').value = posts.join(\',\');
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
                if(!grid.selPostsIds){
                    grid.selPostsIds = {};
                }

                var trElement   = Event.findElement(event, "tr");
                var isInput     = Event.element(event).tagName == \'INPUT\';
                var inputs      = Element.select(trElement, \'input\');
                var checkbox    = inputs[0];
                var position    = inputs[1].value || 1;
                var checked     = isInput ? checkbox.checked : !checkbox.checked;
                checkbox.checked = checked;
                var postId    = checkbox.value;

                if(checked){
                     grid.selPostsIds[postId] = position;
                }
                else{
                    delete(grid.selPostsIds[postId]);
                }

                var idsclone = Object.clone(grid.selPostsIds);
                var bans = Object.keys(grid.selPostsIds);
                var pos = Object.values(grid.selPostsIds).sort(sortNumeric);
                var posts = [];
                var k = 0;
                for(var j = 0; j < pos.length; j++){
                    for(var i = 0; i < bans.length; i++){
                        if(idsclone[bans[i]] == pos[j]){
                            posts[k] = bans[i];
                            k++;
                            delete(idsclone[bans[i]]);
                            break;
                        }
                    }
                }
                $(\'' .
            $this->_elementValueId .
            '\').value = posts.join(\',\');
                grid.reloadParams = {};
                grid.reloadParams[\'selected_posts[]\'] = posts;
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
                    if(!grid.selPostsIds){
                        grid.selPostsIds = {};
                    }
                    var checkbox    = element;

                    checkbox.checked = checked;
                    var postId    = checkbox.value;
                    if(postId == \'on\'){
                        return;
                    }
                    var trElement   = element.up(\'tr\');
                    var inputs      = Element.select(trElement, \'input\');
                    var position    = inputs[1].value || 1;

                    if(checked){
                        if(Object.keys(grid.selPostsIds).indexOf(postId) < 0){
                            grid.selPostsIds[postId] = position;
                        }
                    }
                    else{
                        delete(grid.selPostsIds[postId]);
                    }

                    var idsclone = Object.clone(grid.selPostsIds);
                    var bans = Object.keys(grid.selPostsIds);
                    var pos = Object.values(grid.selPostsIds).sort(sortNumeric);
                    var posts = [];
                    var k = 0;
                    for(var j = 0; j < pos.length; j++){
                        for(var i = 0; i < bans.length; i++){
                            if(idsclone[bans[i]] == pos[j]){
                                posts[k] = bans[i];
                                k++;
                                delete(idsclone[bans[i]]);
                                break;
                            }
                        }
                    }
                    $(\'' .
            $this->_elementValueId .
            '\').value = posts.join(\',\');
                    grid.reloadParams = {};
                    grid.reloadParams[\'selected_posts[]\'] = posts;
                }';
    }

    /**
     * Adds additional parameter to URL for loading only posts grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'digidirect_blog/post_widget/chooser',
            [
                'posts_grid' => true,
                '_current' => true,
                'uniq_id' => $this->getId(),
                'selected_posts' => join(',', $this->getSelectedPosts())
            ]
        );
    }

    /**
     * Setter
     *
     * @param array $selectedPosts
     * @return $this
     */
    public function setSelectedPosts($selectedPosts)
    {
        if (is_string($selectedPosts)) {
            $selectedPosts = explode(',', $selectedPosts);
        }
        $this->_selectedPosts = $selectedPosts;
        return $this;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getSelectedPosts()
    {
        if ($selectedPosts = $this->getRequest()->getParam('selected_posts', $this->_selectedPosts)) {
            $this->setSelectedPosts($selectedPosts);
        }
        return $this->_selectedPosts;
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
     * Set custom filter for in post flag
     *
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_posts') {
            $postIds = $this->getSelectedPosts();
            if (empty($postIds)) {
                $postIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addPostIdsFilter($postIds);
            } else {
                if ($postIds) {
                    $this->getCollection()->addPostIdsFilter($postIds, true);
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
