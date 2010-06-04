<?php
/**
 * OpenID_ServiceEndpoint
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
require_once 'OpenID/Discover.php';

/**
 * OpenID_ServiceEndpoint
 *
 * A simple class that represents a single OpenID provider service endpoint.
 *
 * @category  Auth
 * @package   OpenID
 * @author    Rich Schumacher <rich.schu@gmail.com>
 * @copyright 2009 Rich Schumacher
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_ServiceEndpoint
{
    /**
     * An array of URIs for this endpoint
     *
     * @var array
     */
    protected $uris = array();

    /**
     * An array of service types
     *
     * @var array
     */
    protected $types = array();

    /**
     * The local ID represented by this endpoint
     *
     * @var string
     */
    protected $localID = null;

    /**
     * The source of discovery
     *
     * @var string
     */
    protected $source = null;

    /**
     * The version of the OpenID protocol this endpoint supports
     *
     * @var string
     */
    protected $version = null;

    /**
     * Sets the endpoint URIs
     *
     * @param array $uris The endpoint URIs
     *
     * @return void
     */
    public function setURIs(array $uris)
    {
        foreach ($uris as $key => $uri) {
            if (!filter_var($uri, FILTER_VALIDATE_URL)) {
                unset($uris[$key]);
            }
        }

        $this->uris = $uris;
    }

    /**
     * Returns the URIs for this endpoint
     *
     * @return array
     */
    public function getURIs()
    {
        return $this->uris;
    }

    /**
     * Sets the service type
     *
     * @param array $types The service types
     *
     * @return void
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    /**
     * Returns the service types
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Sets the local ID
     *
     * @param string $localID Local ID for this endpoint
     *
     * @return void
     */
    public function setLocalID($localID)
    {
        $this->localID = $localID;
    }

    /**
     * Returns the local ID
     *
     * @return string
     */
    public function getLocalID()
    {
        return $this->localID;
    }

    /**
     * Sets the source of discovery
     *
     * @param string $source The source of discovery
     *
     * @return void
     */
    public function setSource($source)
    {
        if (in_array($source, OpenID_Discover::$discoveryOrder)) {
            $this->source = $source;
        }
    }

    /**
     * Returns the discovery source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Sets the OpenID protocol version this endpoint supports
     *
     * @param string $version The OpenID version
     *
     * @return void
     */
    public function setVersion($version)
    {
        if (array_key_exists($version, OpenID::$versionMap)) {
            $this->version = $version;
        }
    }

    /**
     * Returns the OpenID protocol version this endpoint supports
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Determines if this service endpoint is valid
     *
     * Only checks to ensure that there is at least one valid service URI set
     * for this endpoint.
     *
     * @return bool
     */
    public function isValid()
    {
        return count($this->getURIs()) > 0;
    }
}

?>
