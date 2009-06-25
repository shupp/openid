<?php

require_once 'OpenID.php';
require_once 'Services/Yadis.php';
require_once 'Validate.php';
require_once 'OpenID/ServiceEndpoint.php';
require_once 'OpenID/ServiceEndpoints.php';
require_once 'OpenID/Discover/Exception.php';

class OpenID_Discover
{
    const TYPE_YADIS = 'Yadis';
    const TYPE_HTML  = 'HTML';

    protected $supportedTypes = array(
        self::TYPE_YADIS,
        self::TYPE_HTML
    );

    static public $discoveryOrder = array(
        0  => OpenID_Discover::TYPE_YADIS,
        10 => OpenID_Discover::TYPE_HTML
    );

    protected $identifier = null;

    protected $requestOptions = array(
        'allowRedirects' => true,
        'timeout'        => 3,
        'readTimeout'    => array(3, 0)
    );

    private $services = null;

    public function __construct($identifier)
    {
        libxml_use_internal_errors(true);
        $this->identifier = OpenID::normalizeIdentifier($identifier);
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    public function setRequestOptions(array $options)
    {
        $this->requestOptions = $options;
    }

    public function discover()
    {
        // Sort ascending
        ksort(self::$discoveryOrder);

        foreach (self::$discoveryOrder as $service) {
            try {
                $discover = self::factory($service, $this->identifier);
                $result   = $discover->discover();
            } catch (OpenID_Discover_Exception $e) {
                continue;
            }

            if ($result === false || !isset($result[0])) {
                continue;
            }

            $this->services = $result;

            return true;
        }

        return false;
    }

    static private function factory($discoverType, $identifier)
    {
        $file  = 'OpenID/Discover/' . $discoverType . '.php';
        $class = 'OpenID_Discover_' . $discoverType;

        include_once $file;

        if (!class_exists($class)) {
            throw new OpenID_Discover_Exception(
                'Unable to load driver: ' . $discoverType
            );
        }

        $object = new $class($identifier);

        if (!$object instanceof OpenID_Discover_Interface) {
            throw new OpenID_Discover_Exception(
                'Requested driver does not conform to Discover interface'
            );
        }

        return $object;
    }

    /**
     * Static helper method for retrieving discovered information from cache if it
     * exists, otherwise executing discovery and storing results if they are 
     * positive.
     * 
     * @param string       $id URI Identifier do discover
     * @param OpenID_Store $store  Instance of OpenID_Store
     * 
     * @return OpenID_Discover|false OpenID_Discover on success, false on failure
     */
    static public function getDiscover($id, OpenID_Store_Interface $store)
    {
        $discoverCache = $store->getDiscover($id);

        if ($discoverCache instanceof OpenID_Discover) {
            return $discoverCache;
        }

        $discover = new OpenID_Discover($id);
        $result   = $discover->discover();
        if ($result === false) {
            return false;
        }
        $store->setDiscover($discover);

        return $discover;
    }
}

?>
