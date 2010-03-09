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
 * @link      http://github.com/shupp/openid
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
require_once 'Date.php';

/**
 * OpenID_Discover
 *
 * Implements OpenID discovery ({@link 
 * http://openid.net/specs/openid-authentication-2_0.html#discovery 7.3} of the 2.0
 * spec).  Discovery is driver based, and currently supports YADIS discovery
 * (via Services_Yadis), and HTML discovery ({@link OpenID_Discover_HTML}).  Once 
 * completed, it will also support {@link 
 * http://www.hueniverse.com/hueniverse/2009/03/the-discovery-protocol-stack.html 
 * XRD/LRDD}.
 * 
 * Example usage for determining the OP Endpoint URL:
 * <code>
 * $id = 'http://user.example.com';
 * 
 * $discover = new OpenID_Discover($id);
 * $result   = $discover->discover();
 * 
 * if (!$result) {
 *     echo "Discovery failed\n";
 * } else {
 *     // Grab the highest priority service, and get it's first URI.
 *     $endpoint      = $discover->services[0];
 *     $opEndpointURL = array_shift($serviceEndpoint->getURIs());
 * }
 * </code>
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @author    Rich Schumacher <rich.schu@gmail.com>
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
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
     * HTTP_Request2 options
     * 
     * @var array
     */
    protected $requestOptions = array(
        'adapter'          => 'curl',
        'follow_redirects' => true,
        'timeout'          => 3,
        'connect_timeout'  => 3
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
     * Sets the HTTP_Request2 options to use
     * 
     * @param array $options Array of HTTP_Request2 options
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

            if ($result instanceof OpenID_ServiceEndpoints && isset($result[0])) {
                $this->services = $result;
                return true;
            }
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
     * Determines if dicovered information supports a given OpenID extension
     * 
     * @param string $extension The name of the extension to check, (SREG10, AX, etc)
     * 
     * @return bool
     */
    public function extensionSupported($extension)
    {
        $class = 'OpenID_Extension_' . $extension;
        $file  = str_replace('_', '/', $class) . '.php';

        include_once $file;

        if (!class_exists($class, false)) {
            throw new OpenID_Discover_Exception(
                'Unknown extension: ' . $class
            );
        }

        $instance = new $class(OpenID_Extension::REQUEST);

        foreach ($this->services as $service) {
            if (in_array($instance->getNamespace(), $service->getTypes())) {
                return true;
            }
        }

        return false;
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
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $expireTime = null;
        if ($discover->services->getExpiresHeader()) {
            $tz  = new Date_TimeZone(date_default_timezone_get());
            $now = new Date();
            $now->setTZ($tz);

            $expireDate = new Date(strtotime($discover->services
                                                      ->getExpiresHeader()));
            $span       = new Date_Span($now, $expireDate);
            $expire     = (int)$span->toSeconds();
        }

        $store->setDiscover($discover, $expireTime);

        return $discover;
    }
}

?>
