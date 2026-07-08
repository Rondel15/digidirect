## Description
Component can be used for displaying of the offCanvas element on the page.

## VERSION 1.0.0 
  
## How to display offcanvas on frontend

##### Example of the XML if we have two offCanvas panels on the page:
```xml
<body>
   <attribute name="class" value="-move-body"/>
   <referenceContainer name="root">
       <block class="Magento\Framework\View\Element\Template" name="offcanvas-trigger" template="Magento_Theme::offcanvas-trigger.phtml" />
       <block class="Magento\Framework\View\Element\Template" name="offcanvas-block" template="Magento_Theme::offcanvas-block.phtml />
   </referenceContainer>
</body>
```
            
***-move-body*** - Define effect witch will be applied for a body when panels will be show. Connected with 'moveBodyOnOpen' JS setting. Works only if direction classes added.

##### Example of the trigger and panel elements HTML:
```html
<div class="trigger-offcanvas" data-container="#example-panel-1" data-mage-init='{"offcanvas": {}}'>Trigger of #example-panel-1 panel</div>
<div class="trigger-offcanvas" data-container=".example-panel-2" data-mage-init='{"offcanvas": {"trigger": "[data-action=trigger-offcanvas]"}}'>Trigger of .example-panel-2 panel</div>

<div id="example-panel-1" class="offcanvas-panel offcanvas-left" aria-hidden="true">
    I am the First offcanvas panel.
</div>

<div class="offcanvas-panel offcanvas-right example-panel-2" aria-hidden="true">
    I am the Second offcanvas panel.
    <button type="button" data-container="#example-panel-1" data-action="trigger-offcanvas">Close</button>
</div>
```

***.trigger-offcanvas*** - Selector class used for styling of the trigger elements. <br>
***data-container*** - Data-attribute used for defining of the selector of the panel, which will be switched on action event. <br>
***#example-panel-1***,  ***.example-panel-2*** - Selectors linking panel element with trigger element. <br>
***.offcanvas-panel*** - Class used for styling of the offcanvas panel element and initialization of offcanvas panels in js logic. <br>
***-left***, ***-right*** - CSS class used for defining the side from which panel will be shown. <br>
***aria-hidden*** - Hide content before it becomes active. <br>

##### Offcanvas component Settings:

 Option | Type | Default | Description
 ------ | ---- | ------- | -----------
 offCanvasWrapperSelector | string | 'body' |  Element selector for offcanvas initialization.
 styleClasses.initialisedClass | string  | '-inited' | Class which will be added when offcanvas initialized,
 styleClasses.activeClass | string | '-active' | Class for panels and triggers which will be added in active state.
 styleClasses.wrapperClass | string | '-offcanvas-wrapper' | Class for 'offCanvasWrapperSelector' to apply style for 'moveBodyOnOpen'.
 styleClasses.rightDirectionClass | string | 'offcanvas-right' | Class for panels used to define if right panel present on the page.
 styleClasses.leftDirectionClass | string | 'offcanvas-left' | Class for panels used to define if left panel present on the page.
 styleClasses.offcanvasOpenedClass | string | '-offcanvas-opened' | Class for body used to define that offcanvas has opened state.
 trigger | string | '' | Trigger selector to toggle offCanvas visibility (for example: close button). Don't use the same trigger selector for 2 or more offCanvas panels.
 addOn | string | '' | Add-ons communicate between component and the global store
 closeOnEsc | boolean | true | Defining if offcanvas should be closes on Esc button press.
 moveBodyOnOpen | boolean | false | Defining if body will be moved aside when offcanvas will be shown. NOTE: if option is enabled, make sure '.offcanvas-wrapper' present on the page.

