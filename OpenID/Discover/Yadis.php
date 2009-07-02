<?php
/**
 * OpenID_Discover_Yadis
 * 
 * PHP Version 5.2.0+
 * 
 * @category  Auth
 * @package   OpenID
 * @uses      OpenID_Discover_Interface
 * @uses      OpenID_Discover
 * @author    Rich Schumacher <rich.schu@gmail.com>
 * @copyright 2009 Rich Schumacher
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */

/**
 * Required files 
 */
require_once 'OpenID/Discover.php';
require_once 'OpenID/Discover/Interface.php';
require_once 'OpenID/ServiceEndpoint.php';
require_once 'OpenID/ServiceEndpoints.php';

/**
 * Implements YADIS discovery
 * 
 * @category  Auth
 * @package   OpenID
 * @uses      OpenID_Discover_Interface
 * @uses      OpenID_Discover
 * @author    Rich Schumacher <rich.schu@gmail.com>
 * @copyright 2009 Rich Schumacher
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 * @see       Services_Yadis
 */
class OpenID_Discover_Yadis
extends OpenID_Discover
implements OpenID_Discover_Interface
{
    /**
     * The Services_Yadis instance
     * 
     * @var Services_Yadis
     */
    protected $yadis = null;

    /**
     * Constructor.  Instantiates Services_Yadis, sets the openid namespace, and 
     * sets the HTTP_Request options.
     * 
     * @param string $identifier The user supplied and normalized identifier.
     * 
     * @return void
     */
    public function __construct($identifier)
    {
        $this->yadis = new Services_Yadis($identifier);
        $this->yadis->setHttpRequestOptions($this->requestOptions);
        $this->yadis->addNamespace('openid', 'http://openid.net/xmlns/1.0');
    }

    /**
     * Performs YADIS discovery
     * 
     * @throws OpenID_Discover_Exception on error
     * @return OpenID_ServiceEndpoints
     */
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
