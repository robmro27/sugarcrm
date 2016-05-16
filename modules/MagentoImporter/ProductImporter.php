<?php

/**
 * Description of ProductImporter
 *
 * @author rmroz
 */
class ProductImporter {
    
    const API_URL = 'http://sugarcrm_magento.local/api/V2_soap/?wsdl=1';
    
    const USERNAME = 'sugarcrm';
    const PASSWORD = 'sugarcrm';
    
    public function importProductList()
    {
        
        $cli = new SoapClient(self::API_URL);

        $session_id = $cli->login(self::USERNAME, self::PASSWORD);

        $params = array('complex_filter'=>
            array(array('key'=>'created_at','value'=>array('key' =>'from','value' => $this->getLastSuccessExecution())))
        );

        return $cli->catalogProductList($session_id, $params);
        
    }
    
    
    public function getLastSuccessExecution()
    {
        $db = DBManagerFactory::getInstance();

        $query = 'select * from job_queue jq where jq.name = "Magento Importer" '
               . 'and jq.`status` = "' . SchedulersJob::JOB_STATUS_DONE . '" and jq.resolution = "' . SchedulersJob::JOB_SUCCESS . '"';

        $result = $db->query($query);
        $row = $db->fetchByAssoc($result);
        return ($row['execute_time'] !== null ) ? $row['execute_time'] : date('Y-m-d H:i:s', null) ;
    }
    
}
