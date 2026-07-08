Blog
====================

[wiki link](https://wiki.digidirect.com/display/LEGO/Blog)

### Description
Blog extension for Magento gives you a noteworthy opportunity to communicate with your regular customers and casual visitors.
With Blog you can create an interactive two-way communication platform to provide official information on your store news, upcoming products, promotions, and get customers’ feedback.

### VERSION 1.0.0

  1. Admin is able to configure module
  2. Admin is able to manage posts for blog page in backend of Magento
  3. Admin is able to manage categories for blog page in backend of Magento
  4. Admin is able to manage comments for blog page in backend of Magento
  5. User is able to see blog post list page
  6. User is able to see blog post detail page
  7. User is able to see category listing page
  
  
  Tips: 
    1. If your project has separate mobile theme you can use "mobile" setting:
   
      <referenceContainer name="content">
          <block class="Digidirect\Blog\Block\Blog" name="blog.list" template="blog_list.phtml">
              <block class="Magento\Theme\Block\Html\Pager" name="post_list_pager" as="post_list_pager"/>
                  <arguments>
                      <argument name="limit" xsi:type="helper" helper="Digidirect\Blog\Helper\Data::getPostPerPage"/>
                  </arguments>
              </block>
          </referenceContainer>
      
  
  Replace:        
     
    <argument name="limit" xsi:type="helper" helper="Digidirect\Blog\Helper\Data::getPostPerPage"/>  
  
  With:
  
    <argument name="limit" xsi:type="helper" helper="Digidirect\Blog\Helper\Data::getMobilePostPerPage"/>  
    
### VERSION 1.2.0
  1. Added ability to see blog post hashtags on blog posts listing page
  2. Added ability to navigate through category from blog post detail page  
   
### VERSION 1.2.1
  1. Fixed categories names displaying on post/listing page.
  2. Added commented code to widget templates with examples how to display categories associated with current post
  
### VERSION 1.2.2  

  1. Fixed ui components deprecated usage
  
### VERSION 1.3.2
  1. Fixed router bug.
   
### VERSION 1.3.3
   1. Fixed missed form key issue
   
### VERSION 1.3.5 
  1. Data models now implement identity interface
  2. Blocks are cacheable
  3. Fixed image preview on blog details page in admin
  
### VERSION 1.3.7
  1. Fixed publish date
  
### VERSION 1.3.8
  1. Fixed related products saving
  2. Fixed datetime error 