Digidirect Address Autocomplete
=====================

[wiki link](https://wiki.digidirect.com/display/LEGO/Address+Autocomplete)

### VERSION 1.0.0

Admin is able to enable/disable google autocomplete for store.
User is able to see autocomplete suggestions

### DEVELOPER INFORMATION

To enable google autocomplete functionality at any page with from you need to:
1.  <update handle="digidirect_address_autocomplete_config" />
2. Configure fields mapping in xml(for example):

        <referenceBlock name="digidirect_address_autocomplete">
            <arguments>
                <argument name="input_field" xsi:type="string">street[0]</argument>
                <argument name="form_selector" xsi:type="string">*</argument>
                <argument name="street_field" xsi:type="string">street[0]</argument>
            </arguments>
        </referenceBlock>

### VERSION 1.1.0

Added "AU Post" autocomplete type

### VERSION 1.1.1

Added option for enabling restriction by State

### VERSION 1.1.2

Fixed triggering additional events for 'change' handler
Add init autocomplete on checkout billing-step
Add possibility to init autocomplete by trigger "av_address_form_loaded"
Backend: 
    1) removed using 'customer_address_save_before' and Observer - used aroundExecute for saving address
    2) Added validation for au post on "shipping address" step on checkout page 
    
### VERSION 1.1.3
   Added pre-validation checking if address for current shipping method should not be validated
   
### VERSION 1.1.4
   Autocomplete now is enabled on payment step on checkout

### VERSION 1.1.5
   Fixed State Validation Error
   
### VERSION 1.1.6   
   Bugfix#186688 -- Unit value is removed

### VERSION 1.1.7
   Postcode ranges were associated with States
### VERSION 2.0.0
   Address attributes configuration added in separate grid.
   Postcode and City address attributes validation selected by default and are not be unselected by an admin in current the revision of extension

### VERSION 2.0.1
  bugfix#191681 -- Checkout - New Address: An eternal spinner appears in the City field if enter numbers in it
  
### VERSION 2.0.3 
  1. Requests are being done with method GET
  2. Fixed bug if countries file was not uploaded for enabled country. Previously it was impossible to go through checkout
  
### VERSION 2.0.4
  1. Added configuration with postcode length for each country
  2. Removed restriction to 4 symbols in database for postcode
   
### VERSION 2.0.6
   Bugfix#259887 -- [PROJECT: PLATYPUS][SDD] Delivery method doesn't change after the user clicks on Confirm button to change Delivery method to Standard delivery