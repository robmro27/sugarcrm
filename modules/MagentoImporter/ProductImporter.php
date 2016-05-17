<?php

include_once 'ImporterAbstract.php';

/**
 * Import Products 
 *
 * @author rmroz
 */
class ProductImporter extends ImporterAbstract {
    
    /**
     * Local path for copy of Magento products images
     */
    const LOCAL_IMAGES_DESTINATION = 'C:/xampp/htdocs/sugarcrm/upload/';
    const IMAGES_DESTINATION = '/opt/home/users/rmroz/repos/sugarcrm/upload';
    
    public function __construct() 
    {
        parent::__construct();
    }

    
    /**
     * 
     * @param stdClass  $magentoProduct
     */
    public function importProduct($magentoProduct)
    {
        // get aslo cost attribute
        $attributes = new stdClass();
        $attributes->additional_attributes = array('cost');
        
        // magento product
        $magentoProductInfo = $this->soapClient->catalogProductInfo($this->sessionId, $magentoProduct->product_id, null, $attributes);
        
        // magento product images
        $magentoProductImages = $this->soapClient->catalogProductAttributeMediaList($this->sessionId, $magentoProduct->product_id);

        // copy image
        
        $username = 'polcode';
        $password = 'polcode';
        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Authorization: Basic " . base64_encode("$username:$password")
            )
        ));

        
        $src = $magentoProductImages[0]->url;
        $dest = $this->getDestinationForImageFiles() . basename($src);
        file_put_contents($dest, file_get_contents($src, false, $context)); 
       
        // add product
        $productBean = new oqc_Product();
        $productBean->name = $magentoProductInfo->name;
        $productBean->date_entered = date('Y-m-d');
        $productBean->date_modified = date('Y-m-d');
        $productBean->description = $magentoProductInfo->description;
        $productBean->status = 'New';
        $productBean->price = $magentoProductInfo->price;
        $productBean->cost = $magentoProductInfo->additional_attributes[0]->value;
        $productBean->oqc_vat = 'default';
        $productBean->active = 1;
        $productBean->relatedcategory_id = self::CATEGORY_ID;
        $productBean->unit = 'pieces';
        $productBean->catalog_id = self::CATALOG_ID;
        $productBean->supplier_id = self::SUPPLIER_ID;
        $productBean->image_unique_filename = basename($src);
        $productBean->image_filename = basename($src);
        $productBean->image_mime_type = $this->getMimetype($magentoProductImages[0]->file);
        $productBean->unique_identifier = $magentoProductInfo->sku;
        $productBean->svnumber = $magentoProductInfo->sku;
        $productBean->save();
        
    }
    
    
    /**
     * Gets list of new added product from last succesfull import
     */
    public function importProductList()
    {

        $params = array('complex_filter'=>
            array(array('key'=>'created_at','value'=>array('key' =>'from','value' => $this->getLastSuccessExecution())))
        );

        $magentoProducts = $this->soapClient->catalogProductList($this->sessionId, $params);
        
        $productBean = new oqc_Product();
        foreach ( $magentoProducts as $magentoProduct ) 
        {
            // if product already exists continue
            $sugarProduct = $productBean->retrieve_by_string_fields(array('svnumber' => $magentoProduct->sku ));
            if ($sugarProduct instanceof oqc_Product) {
                continue;
            }
            
            $this->importProduct($magentoProduct);
        }
    }
    
    /**
     * Get destination for copy files
     * @return string
     */
    private function getDestinationForImageFiles()
    {
        if ( $_SERVER['REMOTE_ADDR'] = '127.0.0.1' )
        {
            return self::LOCAL_IMAGES_DESTINATION;
        }
        return self::IMAGES_DESTINATION;
    }
    
    
}
