<?php
/**
 * OpenID_Discover 
 * 
 * PHP Version 5.2.0+
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Rich Schumacher <rich.schu@gmail.com>
 * @copyright 2009 Rich Schumacher
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */

/**
 * Required files
 */
require_once 'OpenID.php';
require_once 'Services/Yadis.php';
require_once 'Validate.php';
require_once 'OpenID/ServiceEndpoint.php';
require_once 'OpenID/ServiceEndpoints.php';
require_once 'OpenID/Discover/Exception.php';

/**
 * OpenID_Discover 
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Discover
{
    const TYPE_YADIS = 'Yadis';
    const TYPE_HTML  = 'HTML';

    /**
     * List of supported discover types
     * 
     * @var array
     */
    protected $supportedTypes = array(
        self::TYPE_YADIS,
        self::TYPE_HTML
    );

    /**
     * Order that discover should be performed 
     *
     * @var array
     */
    static public $discoveryOrder = array(
        0  => OpenID_Discover::TYPE_YADIS,
        10 => OpenID_Discover::TYPE_HTML
    );

    /**
     * The normalized version of the user supplied identifier
     * 
     * @var string
     */
    protected $identifier = null;

    /**
     * HTTP_Request options
     * 
     * @var array
     */
    protected $requestOptions = array(
        'allowRedirects' => true,
        'timeout'        => 3,
        'readTimeout'    => array(3, 0)
    );

    /**
     * Instance of OpenID_ServiceEndpoints
     * 
     * @var OpenID_ServiceEndpoints
     */
    protected $services = null;

    /**
     * Constructor.  Enables libxml internal errors, normalized the identifier.
     * 
     * @param mixed $identifier The user supplied identifier
     * 
     * @return void
     */
    public function __construct($identifier)
    {
        libxml_use_internal_errors(true);
        $this->identifier = OpenID::normalizeIdentifier($identifier);
    }

    /**
     * Gets member variables
     * 
     * @param string $name Name of the member variable to get
     * 
     * @return mixed The member variable if it exists
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    /**
     * Sets the HTTP_Request options to use
     * 
     * @param array $options Array of HTTP_Request options
     * 
     * @return OpenID_Discover for fluent interface
     */
    public function setRequestOptions(array $options)
    {
        $this->requestOptions = $options;
        return $this;
    }

    /**
     * Performs discovery
     * 
     * @return bool true on success, false on failure
     */
    public function discover()
    {
        // Sort ascending
        ksort(self::$discoveryOrder);

        foreach (self::$discoveryOrder as $service) {
            try {
                $discover = self::_factory($service, $this->identifier);
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

    /**
     * Provides the standard factory pattern for loading discovery drivers.
     * 
     * @param string $discoverType The discovery type (driver) to load
     * @param string $identifier   The user supplied identifier
     * 
     * @return void
     */
    static private function _factory($discoverType, $identifier)
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
     * @param string       $id    URI Identifier to discover
     * @param OpenID_Store $store Instance of OpenID_Store
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
