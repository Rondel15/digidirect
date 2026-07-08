Digidirect Vii
=====================

[wiki link](https://wiki.digidirect.com/display/LEGO/Vii+)

The extention allows integrate Magento with Vii system.

If you want to display Credit Card status on MyAccount page, please use following code in the template Digidirect_Abstractgiftcard::view/frontend/templates/check.phtml:
```php
    $card = $block->getCard();
    $helper = $this->helper('Digidirect\Vii\Helper\Data');
    $status = $helper->getCardStatusLabel($card->getCardStatus())
```
 
1.0.0
=============
* New features:
    * [#325975](https://tp.digidirect.com/325975) -- [Vii] As a system, I want CheckBalance interface to be available
    * [#325977](https://tp.digidirect.com/325977) -- [Vii] As a system, I want Redemption interface to be available
    * [#325978](https://tp.digidirect.com/325978) -- [Vii] As a system, I want Undo interface to be available
    * [#325980](https://tp.digidirect.com/325980) -- [Vii] As a system, I want PreAuthRequest interface to be available
    * [#325981](https://tp.digidirect.com/325981) -- [Vii] As a system, I want PreAuthCancellation interface to be available
    * [#325986](https://tp.digidirect.com/325986) -- [Vii] As a user, I want Vii Gift Cart functionality to be available on the Cart, Checkout and My Account pages
    * [#325983](https://tp.digidirect.com/325983) -- [Vii] As an admin, I want the following settings to be available
* Bugfixes:
    * [#342387](https://digidirect.tpondemand.com/entity/342387) -- [Vii] Amount should have two decimal numbers in Vii request

1.1.0
=============
* New features:
    * [#344574](https://digidirect.tpondemand.com/entity/344574) -- [Vii] As an admin, I want following settings to be added
    * [#371299](https://digidirect.tpondemand.com/entity/371299) -- [Vii] As a system, I want Undo interface to be implemented within Vii integration

* Bugfixes:
    * [#351405](https://digidirect.tpondemand.com/entity/351405) -- [Vii] The amount must have 2 digital locations for all requests in the Vii
    * [#354275](https://digidirect.tpondemand.com/entity/354275) -- [Vii] Errors occur when performing a Cron associated with PreAuthCancellation
    * [#357665](https://digidirect.tpondemand.com/entity/357665) -- [Vii] Invalid Token appears in log when cron jobs push the fail process to the queue
    * [#358716](https://digidirect.tpondemand.com/entity/358716) -- [Vii] Error 500 appears if entered invalid Gift Card
    * [#358854](https://digidirect.tpondemand.com/entity/358854) -- [Vii] If the grand total is 0 the PreAuthCancellation should not come on the PayPal order review page
    * [#373723](https://digidirect.tpondemand.com/entity/373723) -- [Vii] "Undo" request gets 500 error when a user tries to place an order with grand total=0 using a card by Vii for testing
    * [#373942](https://digidirect.tpondemand.com/entity/373942) -- [Vii] A message for the client does not appear about an error with GiftCards in the admin panel when creating an invoice
    * [#373966](https://digidirect.tpondemand.com/entity/373966) -- [Vii] A critical error with Undo is reflected in the logs in admin panel
    * [#376704](https://digidirect.tpondemand.com/entity/376704) -- [Vii] PreAuth request is sent to Vii server when there are no money on the gift card