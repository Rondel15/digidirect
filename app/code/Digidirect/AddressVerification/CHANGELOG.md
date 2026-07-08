1.0.0
=============
* Solution Architecture:
    * [#144865](https://digidirect.tpondemand.com/entity/144865) -- Address Autocomplete. Solution Architecture
* New features:
    * [#144866](https://digidirect.tpondemand.com/entity/144866) -- FRONTEND PAGES. As a user, I want to be able to select an address from the list of addresses
    * [#144863](https://digidirect.tpondemand.com/entity/144863) -- GENERAL SETTINGS. As an admin, I want to be able to specify the following parameters for the extension (see description)

1.1.0
=============
* New features:
    * [#173527](https://digidirect.tpondemand.com/entity/173527) -- ENABLE AU POST. As an admin, I want Suburb & Postcodes autocomplete to be configurable
    * [#173526](https://digidirect.tpondemand.com/entity/173526) -- AU POST FILE. As an admin, I want to be able to upload the file with suburb and postcodes in the back office
    * [#173504](https://digidirect.tpondemand.com/entity/173504) -- AU POST FRONTEND. As a user, I want Suburb & Postcodes fields to be combined when entering address on the website and autosuggesting drop-down to be shown when I start entering the value in the field
    * [#165668](https://digidirect.tpondemand.com/entity/165668) -- TIP GOOGLE API KEY. As an admin, I want to see "Google API Key" the label instead of "Browser API Key" in backend
    * [#165414](https://digidirect.tpondemand.com/entity/165414) -- ADDRESS COUNTRY. As a user, I want to be able to choose an address from any country in the autocomplete dropdown on entering an address

* Bug fixes:
    * [#172610](https://digidirect.tpondemand.com/entity/172610) -- Address fields are removed if google dropdown doesn't contain addresses
    * [#179892](https://digidirect.tpondemand.com/entity/179892) -- Addresses only from one country are available in dropdown
    
    
1.1.1
=============
* New features:
    * [#181661](https://digidirect.tpondemand.com/entity/181661) -- RESTRICTION BY STATE. As a user, I want that after choosing the State, values that allowed in Postcode field and Suburb field should NOT be restricted corresponding to entered State in AU POST


1.1.2
=============
* Bug fixes:
    * [#183453](https://digidirect.tpondemand.com/entity/183453) -- Validation message is not removed for 'Zip/Postal Code'
    * [#183954](https://digidirect.tpondemand.com/entity/183954) -- logged in user can't pay with Paypal
    * [#179033](https://digidirect.tpondemand.com/entity/179033) -- Autocomplete doesn't work for 'Payment step' on checkout
    
1.1.3
=============
* Bug fixes
    *[#184789](https://digidirect.tpondemand.com/entity/184789) - Address autocomplete + C&C: Dummy shipping address is not used when admin chooses "Enable Suburb&Postcode Autocomplete

1.1.4
=============
* Bug fixes
    *[#179033](https://digidirect.tpondemand.com/entity/179033) - Autocomplete doesn't work for 'Payment step' on checkout
    
1.1.5
=============
* Bug fixes
    *[#187423](https://digidirect.tpondemand.com/entity/187423) - Project: Platypus. State Validation Error  
     
1.1.6     
=============
* Bugfixes:
    * [#186688](https://digidirect.tpondemand.com/entity/186688) -- Google. Unit value is removed

1.1.7
=============
* New features:
    * [#190439](https://digidirect.tpondemand.com/entity/190439) -- [AU POST FILE] As a system, I want postcode ranges to be associated with States as described

2.0.0
=============
* New features:
    * [#190629](https://digidirect.tpondemand.com/entity/190629) -- [FRONTEND PAGES] As a user, I want to be able to select an address from the list of addresses
    * [#191272](https://digidirect.tpondemand.com/entity/191272) -- As an Admin, I'd like to be able to configure the set of address fields which will be validated on country level
    * [#191265](https://digidirect.tpondemand.com/entity/191265) -- As an Admin, I'd like to be able to download to the system an AU post dat file for a country level

2.0.1
=============
* Bugfixes:
    * [#191681](https://digidirect.tpondemand.com/entity/191681) -- Checkout - New Address: An eternal spinner appears in the City field if enter numbers in it
    
2.0.2
==============
* Bugfixes:
    * [#220279](https://digidirect.tpondemand.com/entity/220279) -- Address Autocomplete: AU POST: No address may be submitted if no .dat file is provided for the country allowed   

2.0.3
==============
* Bugfixes:
    * [#223662](https://digidirect.tpondemand.com/entity/223662) -- Address Autocomplete: AU POST: Error returned on attempt to proceed with shipping address for the country which has no .dat file uploaded
    * [#223866](https://digidirect.tpondemand.com/entity/223866) -- Address verification: search request should be GET, in order to be cashed   
     
2.0.4
===============
* Bugfixes:
    * [#223473](https://digidirect.tpondemand.com/entity/223473) -- field 'post' is limited for only 4 symbols in table 'digidirect_addressverification_locations'
     
2.0.6
===============
* Bugfixes:
    * [#259887](https://digidirect.tpondemand.com/entity/259887) -- [PROJECT: PLATYPUS][SDD] Delivery method doesn't change after the user clicks on Confirm button to change Delivery method to Standard delivery
	
2.0.7
================
* Bugfixes:
    * [#274468](https://digidirect.tpondemand.com/entity/274468) -- [PROJECT: PLATYPUS][AddressVerification] 05/10/18 postcode length validation
    * [#286555](https://digidirect.tpondemand.com/entity/286555) -- [Project: Platypus] Address is not saved in quote for C&C	
	
2.0.9
================
* Bugfixes:
    * [#291951](https://digidirect.tpondemand.com/entity/291951) -- [Project: TAF] [addressverification] no check if a file_element already exists	
	* [#292506](https://digidirect.tpondemand.com/entity/292506) -- [PROJECT: CONVERSE] digidirect/addressverification error
     
2.0.10
===================	
* Bugfixes:
    * [#295129](https://digidirect.tpondemand.com/entity/295129) -- [PROJECT: VANS NZ] Address autocomplete is not working    	 
	
2.0.11
===================
* Bugfixes:
    * [#303081](https://digidirect.tpondemand.com/entity/303081) -- [Project: DigiDirect] JS error message is displayed in the browser's console after opening C&C popup on the Checkout page	