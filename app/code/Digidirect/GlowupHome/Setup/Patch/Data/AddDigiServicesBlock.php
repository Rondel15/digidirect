<?php
namespace Digidirect\GlowupHome\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddDigiServicesBlock implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $content = <<<HTML
<section class="digiservices-section">
    <div class="digiservices-header">
      <h2><span class="digi-orange-text">digi</span><span class="digi-black-text">Services.</span>Do even more with digi products and services.</h2>
    </div>

    <div class="digiservices-grid">

      <div class="digiservice-card digiservice-card-rent">
        <img src="{{media url='wysiwyg/glowup-digiservices/1.png'}}" alt="digiRent" />
        <div class="digicard-body">
          <div class="digicard-header"><h1><span class="digi-orange-text">digi</span><span class="digi-black-text" >Rent</span></h1><a href="#" class="btn">Learn More</a></div>
          <p>Your ultimate flexible rental solution.</p>
        </div>
      </div>

      <div class="digiservice-card digiservice-card-seconds">
        <img src="{{media url='wysiwyg/glowup-digiservices/4.png'}}" alt="digiSeconds" />
        <div class="digicard-body">
          <div class="digicard-header"><h1><span class="digi-orange-text">digi</span><span class="digi-black-text" >Seconds</span></h1><a href="#" class="btn orange">shop now</a></div>
          <p>Save money on Pre-Loved, Open Box & Refurbished Equipments.</p>
        </div>
      </div>

    <div class="digiservice-card digiservice-card-trade">
        <img src="{{media url='wysiwyg/glowup-digiservices/7.png'}}" alt="Trade up program" />
        <div class="digicard-body">
          <div class="digicard-header"><h1><span class="digi-orange-text">Trade</span><span class="digi-black-text" >Up</span><span class="digi-black-text small">Program</span></h1><a href="#" class="btn orange">Trade Now</a></div>
          <p>digiDirect offers a Click & Collect service which allows you to shop and pay for your order online then pick it up at a time and place that may be more convenient to you.</p>
        </div>
      </div>

      <div class="digiservice-card digiservice-card-life">
        <img src="{{media url='wysiwyg/glowup-digiservices/10.png'}}" alt="digiLife" />
        <div class="digicard-body">
          <div class="digicard-header"><h1><span class="digi-orange-text">digi</span><span class="digi-black-text" >Life</span></h1><a href="#" class="btn orange">Join</a></div>
          <p>A photography community built to educate people on how to use their camera equipment and master their settings!</p>
        </div>
      </div>

     <div class="digiservice-card digiservice-card-print">
        <img src="{{media url='wysiwyg/glowup-digiservices/2.png'}}" alt="digiPrint" />
        <div class="digicard-body">
          <div class="digicard-header"><h1><span class="digi-orange-text">digi</span><span class="digi-black-text" >Print</span></h1><a href="#" class="btn">shop now</a></div>
          <p>A world of options for printing & preserving your photographs.</p>
        </div>
      </div>

<div class="digiservice-card digiservice-card-click-collect">
        <img src="{{media url='wysiwyg/glowup-digiservices/5.png'}}" alt="click & collect" />
        <div class="digicard-body">
          <div class="digicard-header"><h1><span class="digi-black-text" >Click & Collect</span></h1><a href="#" class="btn orange">Learn More</a></div>
          <p>digiDirect offers a Click & Collect service which allows you to shop and pay for your order online then pick it up at a time and place that may be more convenient to you.</p>
        </div>
      </div>

 <div class="digiservice-card digiservice-card-club">
        <img src="{{media url='wysiwyg/glowup-digiservices/8.png'}}" alt="digiClub" />
        <div class="digicard-body">
          <div class="digicard-header"><h1><span class="digi-orange-text">digi</span><span class="digi-black-text" >Club</span><span class="registered">®</span></h1><a href="#" class="btn orange">join</a></div>
          <p>And unlock ultimate benefits.</p>
        </div>
      </div>

 <div class="digiservice-card digiservice-card-events">
        <img src="{{media url='wysiwyg/glowup-digiservices/11.png'}}" alt="Events" />
        <div class="digicard-body">
          <div class="digicard-header"><h1><span class="digi-black-text" >Events</span></h1><a href="#" class="btn orange">Explore</a></div>
          <p>Join exclusive photography events, workshops, and in-store sessions across Australia.</p>
        </div>
      </div>

      <div class="digiservice-card digiservice-card-market">
        <img src="{{media url='wysiwyg/glowup-digiservices/3.png'}}" alt="digiMarket" />
        <div class="digicard-body">
          <div class="digicard-header"><h1><span class="digi-orange-text">digi</span><span class="digi-black-text" >Market</span></h1><a href="#" class="btn orange">Explore</a></div>
          <p>Endless aisle of products & categories.</p>
        </div>
      </div>

      <div class="digiservice-card digiservice-card-direct">
        <img src="{{media url='wysiwyg/glowup-digiservices/6.png'}}" alt="digiDirect" />
        <div class="digicard-body">
          <div class="digicard-header"><h1><span class="digi-orange-text">digi</span><span class="digi-black-text" >Direct</span><span class="digi-black-text small">Business</span></h1><a href="#" class="btn orange">Enter now</a></div>
          <p>Specially designed to meet each customer's needs as our team goes beyond a one-size-fits-all approach.</p>
        </div>
      </div>

      <div class="digiservice-card digiservice-card-protect">
        <img src="{{media url='wysiwyg/glowup-digiservices/9.png'}}" alt="digiProtect" />
        <div class="digicard-body">
          <div class="digicard-header"><h1><span class="digi-orange-text">digi</span><span class="digi-black-text" >Protect</span><span class="registered">®</span></h1><a href="#" class="btn orange">Learn More</a></div>
          <p>Will add up to 3 years beyond the manufacturer's warranty.</p>
        </div>
      </div>

      <div class="digiservice-card digiservice-card-price">
        <img src="{{media url='wysiwyg/glowup-digiservices/12.png'}}" alt="PriceMatch" />
        <div class="digicard-body">
          <div class="digicard-header"><h1><span class="digi-orange-text">Price</span><span class="digi-black-text" >Match</span></h1><a href="#" class="btn orange">Learn More</a></div>
          <p>digiDirect will price match Authorised Australian competitors which include both physical stores and online retailers..</p>
        </div>
      </div>

    </div>
  </section>
HTML;

        $blockData = [
            'title' => 'digiServices',
            'identifier' => 'digi-services-glow-up',
            'content' => $content,
            'is_active' => 1,
            'stores' => [0]
        ];

        $this->blockFactory->create()->setData($blockData)->save();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public static function getVersion()
    {
        return null;
    }

}
