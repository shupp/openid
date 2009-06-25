<?php

require_once 'OpenID/Discover.php';
require_once 'OpenID/Discover/Interface.php';
require_once 'OpenID/ServiceEndpoint.php';
require_once 'OpenID/ServiceEndpoints.php';

class OpenID_Discover_Yadis extends OpenID_Discover implements OpenID_Discover_Interface
{
    protected $yadis = null;

    public function __construct($identifier)
    {
        $this->yadis = new Services_Yadis($identifier);
        $this->yadis->setHttpRequestOptions($this->requestOptions);
        $this->yadis->addNamespace('openid', 'http://openid.net/xmlns/1.0');
    }

    public function discover()
    {
        try {
            $discoveredServices = $this->yadis->discover();
            if (!$discoveredServices->valid()) {
                return false;
            }

            $service = new OpenID_ServiceEndpoints($this->yadis->getYadisId());

            foreach ($discoveredServices as $discoveredService) {
                $types = $discoveredService->getTypes();
                if (array_key_exists($types[0], OpenID::$versionMap)) {

                    $version  = $types[0];
                    $localID  = null;
                    $localIDs = $discoveredService->getElements('xrd:LocalID');

                    if (!empty($localIDs[0])) {
                        $localID = $localIDs[0];
                    }

                    // Modify version if appropriate
                    if ($localID && $version == OpenID::SERVICE_2_0_SERVER) {
                        $version = OpenID::SERVICE_2_0_SIGINON;
                    }

                    $opEndpoint = new OpenID_ServiceEndpoint();
                    $opEndpoint->setVersion($types[0]);
                    $opEndpoint->setTypes($types);
                    $opEndpoint->setURIs($discoveredService->getUris());
                    $opEndpoint->setLocalID($localID);
                    $opEndpoint->setSource(OpenID_Discover::TYPE_YADIS);
                    
                    $service->addService($opEndpoint);
                }
            }

            return $service;

        } catch (Services_Yadis_Exception $e) {
            // Add logging or observer?
            throw new OpenID_Discover_Exception($e->getMessage());
        }

        // Did the identifier even respond to the initial HTTP request?
        if ($this->yadis->getUserResponse() === false) {
            throw new OpenID_Discover_Exception(
                'No response from identifier'
            );
        }
    }
}

?>
