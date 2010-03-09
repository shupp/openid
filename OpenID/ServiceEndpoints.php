<?php
/**
 * OpenID_ServiceEndpoints
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
require_once 'OpenID/ServiceEndpoint.php';

/**
* OpenID_ServiceEndpoints
*
* This class represents a colleciton of OpenID_ServiceEndpoint objects.  It
* implements several SPL interfaces to make it easy to consume.
*
* @category  Auth
* @package   OpenID
* @author    Rich Schumacher <rich.schu@gmail.com>
* @copyright 2009 Rich Schumacher
* @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
* @link      http://github.com/shupp/openid
*/
class OpenID_ServiceEndpoints implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * Copy of the Expires header from the HTTP request.  Used for customizing cache
     * times
     * 
     * @var string
     */
    private $_expiresHeader = null;

    /**
     * The user-supplied identifier
     *
     * @var string
     */
    private $_identifier = null;

    /**
     * An array of OpenID_ServiceEndpoint objects
     *
     * @var array
     */
    private $_services = array();

    /**
     * Sets the user-supplied identifier and adds a service if one is passed
     *
     * @param string                       $identifier User-supplied identifier
     * @param null|OpenID_Service_Endpoint $spec       Service endpoint object
     *
     * @return void
     */
    public function __construct($identifier, $spec = null)
    {
        $this->setIdentifier($identifier);

        if ($spec instanceof OpenID_ServiceEndpoint) {
            $this->addService($spec);
        }
    }

    /**
     * Sets the user-supplied indentifier
     *
     * @param string $identifier The user-supplied identifier
     *
     * @return void
     */
    public function setIdentifier($identifier)
    {
        $this->_identifier = $identifier;
    }

    /**
     * Returns the user-supplied identifier
     *
     * @return null|string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Adds a service to the services array
     *
     * @param OpenID_ServiceEndpoint $endpoint The service endpoint object
     *
     * @return void
     */
    public function addService(OpenID_ServiceEndpoint $endpoint)
    {
        if (!$endpoint->isValid()) {
            return;
        }

        $this->_services[] = $endpoint;
    }

    /**
     * Returns an ArrayIterator object to traverse the services array
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_services);
    }

    /**
     * Checks to see if the offset exists in the services array
     *
     * @param int $offset The offset to check
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return (!empty($this->_services[$offset]));
    }
    
    /**
     * Returns the value of the services array at the specified offset
     *
     * @param int $offset The offset to retrieve
     *
     * @return null|OpenID_ServiceEndpoint
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return;
        }

        return $this->_services[$offset];
    }

    /**
     * Sets a value in the services array 
     *
     * @param int                    $offset   The offset to set
     * @param OpenID_ServiceEndpoint $endpoint The service object to set
     *
     * @return void
     */
    public function offsetSet($offset, $endpoint)
    {
        if ($endpoint instanceof OpenID_ServiceEndpoint) {
            $this->_services[$offset] = $endpoint;
        }
    }

    /**
     * Removes a particular offset in the services array
     *
     * @param int $offset The offset to remove
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->_services[$offset]);
    }

    /**
     * Returns the number of service endpoints
     *
     * @return int
     */
    public function count()
    {
        return count($this->_services);
    }

    /**
     * Gets the Expires header value
     * 
     * @see $_expiresHeader
     * @see setExpiresHeader()
     * @return string
     */
    public function getExpiresHeader()
    {
        return $this->_expiresHeader;
    }

    /**
     * Sets the Expires header value
     * 
     * @param string $value The Expires header value
     * 
     * @see getExpiresHeader()
     * @return OpenID_ServiceEndpoints
     */
    public function setExpiresHeader($value)
    {
        $this->_expiresHeader = $value;
        return $this;
    }
}

?>
