1.0.0
=============
* Solution Architecture:
    * [#198977](https://digidirect.tpondemand.com/entity/198977) -- Abstract Gift Card Integration. Solution Architecture
* New features:
    * [#203847](https://digidirect.tpondemand.com/entity/203847) -- Abstract Gift Card Integration.GENERAL SETTINGS. As an admin, I want to be able to specify the following parameters for the extension
    * [#204062](https://digidirect.tpondemand.com/entity/204062) -- Abstract Gift Card Integration. As an admin, I want to have pool of commands for gift card services integration
    * [#204158](https://digidirect.tpondemand.com/entity/204158) -- Abstract Gift Card Integration. As a user, I want to be able to use external Gift Card Service

1.0.1
=============
* Bugfixes:
    * [#212177](https://digidirect.tpondemand.com/entity/212177) -- Can't place an order via PayPal

2.0.0
=============
* Improvements:
    * Compatibility with Magento 2.2.* only
* Bugfixes:
    * [#212228](https://digidirect.tpondemand.com/entity/212228) -- Error on 2.2. upgrade

2.1.0
=============
* New features:
    * [#215218](https://digidirect.tpondemand.com/entity/215218) -- As a system, I want SSL version to be set up
* Bugfixes:
    * [#214644](https://digidirect.tpondemand.com/entity/214644) -- It's improssible to create order in back-office
    * [#214702](https://digidirect.tpondemand.com/entity/214702) -- Compilation is finished with errors due to "Class Digidirect\AbstractGiftCard\Model\Method\Logger does not exist"

2.1.1
=============
* Bugfixes:
    * [#214644](https://digidirect.tpondemand.com/entity/214644) -- Logs for 'check balance' and 'redeem' actions can't be defined

2.1.2
=============
* Bugfixes:
    * [#240771](https://digidirect.tpondemand.com/entity/240771) -- Log data even if could not parse response to see details.

2.1.3
=============
* Bugfixes:
    * [#254672](https://digidirect.tpondemand.com/entity/254672) -- Order total is less than gift card amount but full gift card balance redeemed

2.1.4
=============
* Bugfixes:
    * [#278120](https://digidirect.tpondemand.com/entity/278120) -- Inconsistent 500 error while checking WEX giftcard balance
    * [#280924](https://digidirect.tpondemand.com/entity/280924) -- Fatal error on FE: errors from WEX are not processed correctly

2.1.5
=============
* Bugfixes:
    * [#301423](https://digidirect.tpondemand.com/entity/301423) -- Can't Raise Credit Memo in Admin Area - empty error message

2.1.6
=============
* New features:
    * [#296189](https://digidirect.tpondemand.com/entity/296189) -- Work on website level

2.1.7
=============
* Bugfixes:
    * [#310759](https://digidirect.tpondemand.com/entity/310759) -- [TAF][WEX] Identifying parameters do not show the website where the call is made

2.2.0
=============
* New features:
    * [#325975](https://tp.digidirect.com/325975) -- [Vii] As a system, I want CheckBalance interface to be available
    * [#325977](https://tp.digidirect.com/325977) -- [Vii] As a system, I want Redemption interface to be available
    * [#325978](https://tp.digidirect.com/325978) -- [Vii] As a system, I want Undo interface to be available
    * [#325980](https://tp.digidirect.com/325980) -- [Vii] As a system, I want PreAuthRequest interface to be available
    * [#325981](https://tp.digidirect.com/325981) -- [Vii] As a system, I want PreAuthCancellation interface to be available
    * [#325986](https://tp.digidirect.com/325986) -- [Vii] As a user, I want Vii Gift Cart functionality to be available on the Cart, Checkout and My Account pages
    * [#325983](https://tp.digidirect.com/325983) -- [Vii] As an admin, I want the following settings to be available

2.3.0
=============
* New features:
    * [#332413](https://tp.digidirect.com/332413) -- [APTOS GIFT CARDS][INTERFACE] As a system, I want gift cards integration interfaces to be created
    * [#332414](https://tp.digidirect.com/332414) -- [APTOS GIFT CARDS][FRONTEND] As a user, I want to be able to check gift card balance and then apply it

2.3.1
=============
* Bugfixes:
    * [#371972](https://tp.digidirect.com/371972) -- Revert giftcard logic on exception is not executed.
    * [#371972](https://tp.digidirect.com/371972) -- Revert giftcard logic on exception is not executed.

2.3.2
=============
* New features:
    * [#344574](https://tp.digidirect.com/344574) -- Refactoring to customize. -- [Vii] As an admin, I want following settings to be added
    * [#371299](https://tp.digidirect.com/371299) -- Refactoring to customize. -- [Vii] As a system, I want Undo interface to be implemented within Vii integration
