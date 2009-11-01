<?php
/**
 * OpenID_Discover_HTML
 * 
 * PHP Version 5.2.0+
 * 
 * @category  Auth
 * @package   OpenID
 * @uses      OpenID_Discover
 * @uses      OpenID_Discover_Interface
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
 * Implements HTML discovery
 * 
 * @category  Auth
 * @package   OpenID
 * @uses      OpenID_Discover
 * @uses      OpenID_Discover_Interface
 * @author    Rich Schumacher <rich.schu@gmail.com>
 * @copyright 2009 Rich Schumacher
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Discover_HTML
extends OpenID_Discover
implements OpenID_Discover_Interface
{
    /**
     * The normalized identifier
     * 
     * @var string
     */
    protected $identifier = null;

    /**
     * Constructor.  Sets the 
     * 
     * @param mixed $identifier The user supplied identifier
     * 
     * @return void
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Performs HTML discovery.
     * 
     * @throws OpenID_Discover_Exception on error
     * @return OpenID_ServiceEndpoints
     */
    public function discover()
    {
        $response = $this->sendRequest();

        $dom = new DOMDocument();
        $dom->loadHTML($response);

        $xPath = new DOMXPath($dom);
        $query = "/html/head/link[contains(@rel,'openid')]";
        $links = $xPath->query($query);

        $results = array(
            'openid2.provider' => array(),
            'openid2.local_id' => array(),
            'openid.server'    => array(),
            'openid.delegate'  => array()
        );

        foreach ($links as $link) {
            $rels = explode(' ', $link->getAttribute('rel'));
            foreach ($rels as $rel) {
                if (array_key_exists($rel, $results)) {
                    $results[$rel][] = $link->getAttribute('href');
                }
            }
        }

        return $this->buildServiceEndpoint($results);
    }

    /**
     * Builds the service endpoint
     * 
     * @param array $results Array of items discovered via HTML
     * 
     * @return OpenID_ServiceEndpoints
     */
    protected function buildServiceEndpoint(array $results)
    {
        if (count($results['openid2.provider'])) {
            $version = OpenID::SERVICE_2_0_SIGNON;
            if (count($results['openid2.local_id'])) {
                $localID = $results['openid2.local_id'][0];
            }
            $endpointURIs = $results['openid2.provider'];
        } elseif (count($results['openid.server'])) {
            $version      = OpenID::SERVICE_1_1_SIGNON;
            $endpointURIs = $results['openid.server'];
            if (count($results['openid.delegate'])) {
                $localID = $results['openid.delegate'][0];
            }
        } else {
            throw new OpenID_Discover_Exception(
                'Discovered information does not conform to spec'
            );
        }

        $opEndpoint = new OpenID_ServiceEndpoint();
        $opEndpoint->setVersion($version);
        $opEndpoint->setTypes(array($version));
        $opEndpoint->setURIs($endpointURIs);
        $opEndpoint->setSource(OpenID_Discover::TYPE_HTML);

        if (isset($localID)) {
            $opEndpoint->setLocalID($localID);
        }

        return new OpenID_ServiceEndpoints($this->identifier, $opEndpoint);
    }

    /**
     * Sends the request via HTTP_Request
     * 
     * @return string The HTTP response
     */
    protected function sendRequest()
    {
        $request = new HTTP_Request($this->identifier, $this->requestOptions);
        $request->sendRequest();
        $response = $request->getResponseBody();

        if ($request->getResponseCode() !== 200) {
            throw new OpenID_Discover_Exception(
                'Unable to connect to OpenID Provider.'
            );
        }

        return $response;
    }
}

?>
