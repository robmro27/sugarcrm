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
    const IMAGES_DESTINATION = '/opt/home/users/rmroz/repos/sugarcrm/upload/';
    
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

        $productBean = new oqc_Product();
        
        
        if ( count( $magentoProductImages ) > 0 ) {
            
            // copy image
            $username = self::POLCODE_BASE_AUTH_LOGIN;
            $password = self::POLCODE_BASE_AUTH_PASSWORD;
            $context = stream_context_create(array(
                'http' => array(
                    'header'  => "Authorization: Basic " . base64_encode("$username:$password")
                )
            ));

            $src = $magentoProductImages[0]->url;
            $dest = $this->getDestinationForImageFiles() . basename($src);
            file_put_contents($dest, file_get_contents($src, false, $context)); 

            $this->resize(700, $dest, $dest);
            
            $productBean->image_unique_filename = basename($src);
            $productBean->image_filename = basename($src);
            $productBean->image_mime_type = $this->getMimetype($magentoProductImages[0]->file);
        }
       
        // add product
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
            $sugarProduct = $productBean->retrieve_by_string_fields(array('svnumber' => $magentoProduct->sku, 'deleted' => 0 ));
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
        $sapi_type = php_sapi_name();
        if (substr($sapi_type, 0, 3) != 'cli' && $_SERVER['REMOTE_ADDR'] = '127.0.0.1' ) {
            return self::LOCAL_IMAGES_DESTINATION;
        }
        return self::IMAGES_DESTINATION;
    }
    
    
    private function resize($newWidth, $targetFile, $originalFile) {

        $info = getimagesize($originalFile);
        $mime = $info['mime'];

        switch ($mime) {
                case 'image/jpeg':
                        $image_create_func = 'imagecreatefromjpeg';
                        $image_save_func = 'imagejpeg';
                        break;

                case 'image/png':
                        $image_create_func = 'imagecreatefrompng';
                        $image_save_func = 'imagepng';
                        break;

                case 'image/gif':
                        $image_create_func = 'imagecreatefromgif';
                        $image_save_func = 'imagegif';
                        break;

                default: 
                        throw new Exception('Unknown image type.');
        }

        $img = $image_create_func($originalFile);
        list($width, $height) = getimagesize($originalFile);
        $newHeight = ($height / $width) * $newWidth;
        $tmp = imagecreatetruecolor($newWidth, $newHeight);
        
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        if (file_exists($targetFile)) {
                unlink($targetFile);
        }
        $image_save_func($tmp, "$targetFile");
    }
    
}
