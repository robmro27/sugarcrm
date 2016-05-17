<?php

/**
 * Abstract class for base settings used to connect to Magento API
 *
 * @author rmroz
 */
class ImporterAbstract {
    
    /**
     * Base auth caredentials
     */
    const POLCODE_BASE_AUTH_LOGIN = 'polcode';
    const POLCODE_BASE_AUTH_PASSWORD = 'polcode';
    
    /**
     * Defined in SugarCRM - "Magento Products" catalog
     */
    const CATALOG_ID = '14a12a1a-ac11-2d5f-37f4-5739a7e55001';
    
    /**
     * Defined in SugarCRM - "Magento" - supplier
     */
    const SUPPLIER_ID = '433ce46c-13e7-9006-9bd6-5739a7d30a3c';
    
    /**
     * Defined in SugarCRM - "Magento Products" - category
     */
    const CATEGORY_ID = '171238da-073d-c3ab-a617-5739a72d9f3c';
    
    /**
     * Url's of Magento instance 
     */
    const API_URL_LOCAL = 'http://sugarcrm_magento.local/api/V2_soap/?wsdl=1';
    const API_URL = 'http://magentosugarcrm.rmroz.sites.polcode.net/index.php/api/V2_soap/?wsdl=1';
    
    /**
     * Caredentials
     */
    const USERNAME = 'sugarcrm';
    const PASSWORD = 'sugarcrm';
    
    /**
     *
     * @var SoapClient 
     */
    protected $soapClient = null;
    
    
    /**
     * 
     * @var string 
     */
    protected $sessionId = null;
    
    
    /**
     * Initaialize Soap client if not exist
     */
    public function __construct() {
        
        if ( $this->soapClient == null || $this->sessionId == null ) {
            $this->soapClient = new SoapClient($this->getApiUrl(), array('login' => self::POLCODE_BASE_AUTH_LOGIN, 'password' => self::POLCODE_BASE_AUTH_PASSWORD));
            $this->sessionId = $this->soapClient->login(self::USERNAME, self::PASSWORD);
        }
        
    }
    
    
    /**
     * Gets API Url for server or localhost
     * @return string
     */
    private function getApiUrl()
    {
        $sapi_type = php_sapi_name();
        if (substr($sapi_type, 0, 3) != 'cli' && $_SERVER['REMOTE_ADDR'] = '127.0.0.1' ) {
            return self::API_URL_LOCAL;
        }
        return self::API_URL;
    }
    
    
    
    /**
     * Gets limit from where to import
     * @return string
     */
    public function getImportTimeLimit()
    {
        return date('Y-m-d',(strtotime ( '-1 day' , strtotime ( date('Y-m-d')) ) )) ;
    }
    
    
    
    /**
     * Gets image mime type
     * @param string $file
     * @return string
     */
    public function getMimetype($file) 
    {
        $mime_types = array(
                "gif"  =>  "image/gif",
                "png"  =>  "image/png",
                "jpeg" =>  "image/jpg",
                "jpg"  =>  "image/jpg",
                
        );

        $extension = strtolower(end(explode('.',$file)));

        return $mime_types[$extension];
    }
    
    
    
}
