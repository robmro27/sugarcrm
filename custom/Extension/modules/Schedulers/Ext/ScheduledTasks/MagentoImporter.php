<?php

require_once('modules/MagentoImporter/ProductImporter.php');
require_once('modules/MagentoImporter/ContactImporter.php');

array_push($job_strings, 'MagentoImporter');


function MagentoImporter()
{
    
    $productImporter = new ProductImporter();
    $productImporter->importProductList();
    
    $contactsImporter = new ContactImporter();
    $contactsImporter->importContactList();
    
    return true;
}
