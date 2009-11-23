<?php
/**
 * OpenID 
 * 
 * PHP Version 5.2.0+
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */

/**
 * Required files
 */
require_once 'OpenID/Exception.php';
require_once 'HTTP/Request2.php';
require_once 'Validate.php';
require_once 'OpenID/Message.php';
require_once 'OpenID/Store.php';

/**
 * OpenID 
 * 
 * Base OpenID class.  Contains common constants and helper static methods, as well
 * as the directRequest() method, which handles direct communications.  It also
 * is a common place to assign your custom Storage class and Observers.
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 * @see       OpenID_Observer_Common
 * @see       OpenID_Store
 */
class OpenID
{
    /**
     *  OP identifier constants
     */
    const NS_2_0 = 'http://specs.openid.net/auth/2.0';
    const NS_1_1 = 'http://openid.net/signon/1.1';

    const NS_2_0_ID_SELECT = 'http://specs.openid.net/auth/2.0/identifier_select';

    const SERVICE_2_0_SERVER = 'http://specs.openid.net/auth/2.0/server';
    const SERVICE_2_0_SIGNON = 'http://specs.openid.net/auth/2.0/signon';
    const SERVICE_1_1_SIGNON = 'http://openid.net/signon/1.1';
    const SERVICE_1_0_SIGNON = 'http://openid.net/signon/1.0';

    /**
     * A map of which service types (versions) map to which protocol version.  1.0
     * is mapped to 1.1.  This is mostly helpful to see if openid.ns is supported.
     *
     * @var $versionMap 
     */
    static public $versionMap = array(
        self::SERVICE_2_0_SERVER => self::NS_2_0,
        self::SERVICE_2_0_SIGNON => self::NS_2_0,
        self::SERVICE_1_1_SIGNON => self::NS_1_1,
        self::SERVICE_1_0_SIGNON => self::NS_1_1,
    );

    /**
     * Supported Association Hash Algorithms (preferred)
     */
    const HASH_ALGORITHM_2_0 = 'SHA256';
    const HASH_ALGORITHM_1_1 = 'SHA1';

    /**
     * OpenID Modes
     */
    const MODE_ASSOCIATE            = 'associate';
    const MODE_CHECKID_SETUP        = 'checkid_setup';
    const MODE_CHECKID_IMMEDIATE    = 'checkid_immediate';
    const MODE_CHECK_AUTHENTICATION = 'check_authentication';
    const MODE_ID_RES               = 'id_res';
    const MODE_CANCEL               = 'cancel';
    const MODE_SETUP_NEEDED         = 'setup_needed';
    const MODE_ERROR                = 'error';

    /*
     * Association constants
     */
    const SESSION_TYPE_NO_ENCRYPTION = 'no-encryption';
    const SESSION_TYPE_DH_SHA1       = 'DH-SHA1';
    const SESSION_TYPE_DH_SHA256     = 'DH-SHA256';

    const ASSOC_TYPE_HMAC_SHA1   = 'HMAC-SHA1';
    const ASSOC_TYPE_HMAC_SHA256 = 'HMAC-SHA256';

    /**
     * Instance of OpenID_Store_Interface
     *
     * @var $store
     * @see setStore()
     */
    static protected $store = null;

    /**
     * Array of attached observers
     *
     * @var $observers
     */
    static protected $observers = array();

    /**
     * Stores the last event
     *  
     * @var $lastEvent
     */
    static protected $lastEvent = array(
        'name' => 'start',
        'data' => null
    );

    /**
     * Attaches an observer
     * 
     * @param OpenID_Observer_Common $observer Observer object
     * 
     * @see OpenID_Observer_Log
     * @return void
     */
    static public function attach(OpenID_Observer_Common $observer)
    {
        foreach (self::$observers as $attached) {
            if ($attached === $observer) {
                return;
            }
        }
        self::$observers[] = $observer;
    }

    /**
     * Detaches the observer
     * 
     * @param OpenID_Observer_Common $observer Observer object
     * 
     * @return void
     */
    static public function detach(OpenID_Observer_Common $observer)
    {
        foreach (self::$observers as $key => $attached) {
            if ($attached === $observer) {
                unset(self::$observers[$key]);
                return;
            }
        }
    }

    /**
     * Notifies all observers of an event
     * 
     * @return void
     */
    static public function notify()
    {
        foreach (self::$observers as $observer) {
            $observer->update(self::getLastEvent());
        }
    }

    /**
     * Sets the last event and notifies the observers
     * 
     * @param string $name Name of the event
     * @param mixed  $data The event's data
     * 
     * @return void
     */
    static public function setLastEvent($name, $data)
    {
        self::$lastEvent = array(
            'name' => $name,
            'data' => $data
        );
        self::notify();
    }

    /**
     * Gets the last event
     * 
     * @return void
     */
    static public function getLastEvent()
    {
        return self::$lastEvent;
    }

    /**
     * Sets a custom OpenID_Store_Interface object
     * 
     * @param OpenID_Store_Interface $store Custom storage instance
     * 
     * @return void
     */
    static public function setStore(OpenID_Store_Interface $store)
    {
        self::$store = $store;
    }

    /**
     * Gets the OpenID_Store_Interface instance.  If none has been set, then the 
     * default store is used (CacheLite).
     * 
     * @return OpenID_Store_Interface
     */
    static public function getStore()
    {
        if (!self::$store instanceof OpenID_Store_Interface) {
            self::$store = OpenID_Store::factory();
        }

        return self::$store;
    }

    /**
     * Sends a direct HTTP request.
     * 
     * @param string         $url     URL to send the request to
     * @param OpenID_Message $message Contains message contents
     * @param array          $options Options to pass to HTTP_Request2
     * 
     * @see getHTTPRequest2Instance()
     * @throws OpenID_Exception if send() fails
     * @return HTTP_Request2_Response
     */
    public function directRequest($url,
                                  OpenID_Message $message, 
                                  array $options = array())
    {
        $request = $this->getHTTPRequest2Instance();
        $request->setConfig($options);
        $request->setURL($url);
        // Require POST, per the spec
        $request->setMethod(HTTP_Request2::METHOD_POST);
        $request->setBody($message->getHTTPFormat());
        try {
            return $request->send();
        } catch (HTTP_Request2_Exception $e) {
            throw new OpenID_Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Instantiates HTTP_Request2.  Abstracted for testing.
     * 
     * @see directRequest()
     * @return HTTP_Request2_Response
     */
    protected function getHTTPRequest2Instance()
    {
        // @codeCoverageIgnoreStart
        return new HTTP_Request2();
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns an array of the 5 XRI globals symbols
     * 
     * @return void
     */
    static public function getXRIGlobalSymbols()
    {
        return array('=', '@', '+', '$', '!');
    }

    /**
     * Normalizes an identifier (URI or XRI)
     * 
     * @param mixed $identifier URI or XRI to be normalized
     * 
     * @throws OpenID_Exception on invalid identifier
     * @return string Normalized Identifier.
     */
    static public function normalizeIdentifier($identifier)
    {
        // XRI
        if (preg_match('@^xri://@i', $identifier)) {
            return preg_replace('@^xri://@i', '', $identifier);
        }

        if (in_array($identifier[0], self::getXRIGlobalSymbols())) {
            return $identifier;
        }

        // URL
        if (!preg_match('@^http[s]?://@i', $identifier)) {
            $identifier = 'http://' . $identifier;
        }
        if (Validate::uri($identifier)) {
            return $identifier;
        }
        throw new OpenID_Exception('Invalid URI Identifier');
    }

}
?>
