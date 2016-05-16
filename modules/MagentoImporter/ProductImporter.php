<?php

include_once 'ImporterAbstract.php';

/**
 * Description of ProductImporter
 *
 * @author rmroz
 */
class ProductImporter extends ImporterAbstract {
    
    public function __construct() 
    {
        parent::__construct();
    }

    public function importProduct($magentoProduct)
    {
        
        // TODO: tax rates
        // TODO: copy files between servers
        
        $attributes = new stdClass();
        $attributes->additional_attributes = array('cost');
        
        $magentoProductInfo = $this->soapClient->catalogProductInfo($this->sessionId, $magentoProduct->product_id, null, $attributes);
        $magentoProductImages = $this->soapClient->catalogProductAttributeMediaList($this->sessionId, $magentoProduct->product_id);

        $productBean = new oqc_Product();
        $productBean->name = $magentoProductInfo->name;
        $productBean->date_entered = date('Y-m-d');
        $productBean->date_modified = date('Y-m-d');
        $productBean->description = $magentoProductInfo->description;
        $productBean->status = 'New';
        $productBean->price = $magentoProductInfo->price;
        $productBean->cost = $magentoProductInfo->additional_attributes[0]->value;
        $productBean->active = 1;
        $productBean->relatedcategory_id = self::CATEGORY_ID;
        $productBean->unit = 'pieces';
        $productBean->catalog_id = self::CATALOG_ID;
        $productBean->supplier_id = self::SUPPLIER_ID;
        $productBean->image_filename = $magentoProductImages[0]->url;
        $productBean->image_mime_type = $this->getMimetype($magentoProductImages[0]->file);
        $productBean->unique_identifier = $magentoProductInfo->sku;
        $productBean->svnumber = $magentoProductInfo->sku;
        $productBean->save();
        
    }
    
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
    
    
}
