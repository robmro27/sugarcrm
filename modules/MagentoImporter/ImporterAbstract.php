<?php


/**
 * Description of ImporterAbstract
 *
 * @author rmroz
 */
class ImporterAbstract {
    
    
    const CATALOG_ID = '14a12a1a-ac11-2d5f-37f4-5739a7e55001';
    const SUPPLIER_ID = '433ce46c-13e7-9006-9bd6-5739a7d30a3c';
    const CATEGORY_ID = '171238da-073d-c3ab-a617-5739a72d9f3c';
    
    const API_URL = 'http://sugarcrm_magento.local/api/V2_soap/?wsdl=1';
    
    const USERNAME = 'sugarcrm';
    const PASSWORD = 'sugarcrm';
    
    protected $soapClient = null;
    protected $sessionId;
    
    public function __construct() {
        
        $this->soapClient = new SoapClient(self::API_URL);
        $this->sessionId = $this->soapClient->login(self::USERNAME, self::PASSWORD);
        
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
    
    
    
    
    public function getMimetype($file) 
    {
        $mime_types = array(
                "pdf"   =>  "application/pdf"
                ,"exe"  =>  "application/octet-stream"
                ,"zip"  =>  "application/zip"
                ,"docx" =>  "application/msword"
                ,"doc"  =>  "application/msword"
                ,"xls"  =>  "application/vnd.ms-excel"
                ,"ppt"  =>  "application/vnd.ms-powerpoint"
                ,"gif"  =>  "image/gif"
                ,"png"  =>  "image/png"
                ,"jpeg" =>  "image/jpg"
                ,"jpg"  =>  "image/jpg"
                ,"mp3"  =>  "audio/mpeg"
                ,"wav"  =>  "audio/x-wav"
                ,"mpeg" =>  "video/mpeg"
                ,"mpg"  =>  "video/mpeg"
                ,"mpe"  =>  "video/mpeg"
                ,"mov"  =>  "video/quicktime"
                ,"avi"  =>  "video/x-msvideo"
                ,"3gp"  =>  "video/3gpp"
                ,"css"  =>  "text/css"
                ,"jsc"  =>  "application/javascript"
                ,"js"   =>  "application/javascript"
                ,"php"  =>  "text/html"
                ,"htm"  =>  "text/html"
                ,"html" =>  "text/html"
        );

        $extension = strtolower(end(explode('.',$file)));

        return $mime_types[$extension];

    }
    
    
    
}
