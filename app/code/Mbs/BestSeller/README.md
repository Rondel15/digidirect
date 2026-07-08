# README #

Enhance Magento to display product category listing ordered by best sellers


### How do I get set up? ###

1. clone the repository
2. create folder app/code/Mbs/BestSeller when located at the root of the Magento site
3. copy the content of this repository within the folder
4. install the module php bin/magento setup:upgrade

- Place some orders on items that are deemed to be best sellers
- modify a product from the backend so that the attribute Best Sellers has a high value 
and then going into a category that has the product should render the products with the highest number of sales at the top 

this module changes the default sort order in category product listing to be descending. This is so that the highest the best seller number is, the higher in the ranking the product ends up. This could need to be customised for website that needs another field to be used for default sorting feature



