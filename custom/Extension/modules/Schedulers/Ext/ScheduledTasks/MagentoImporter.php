<?php

require_once('modules/MagentoImporter/ProductImporter.php');
array_push($job_strings, 'MagentoImporter');


function MagentoImporter()
{
    
    $productImporter = new ProductImporter();
    
    
    var_dump($productImporter->importProductList());
    die;
    
    return true;
}
