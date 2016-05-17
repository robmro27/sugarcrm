<?php

require_once('modules/MagentoImporter/ProductImporter.php');
require_once('modules/MagentoImporter/ContactImporter.php');

array_push($job_strings, 'MagentoImporter');


/**
 * Import products and contacts from Magento Instance
 * @return boolean
 */
function MagentoImporter()
{
    
    // import products
    $productImporter = new ProductImporter();
    $productImporter->importProductList();
    
    // import contacts
    $contactsImporter = new ContactImporter();
    $contactsImporter->importContactList();
    
    return true;
}
