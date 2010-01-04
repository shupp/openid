<?php
/**
 * OpenID_Extension 
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
require_once 'OpenID/Extension/Exception.php';

/**
 * OpenID_Extension 
 * 
 * Base class for creating OpenID message extensions.
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
abstract class OpenID_Extension
{
    const REQUEST  = 'request';
    const RESPONSE = 'response';

    /**
     *  @var array Array of reserved message keys
     */
    static protected $reserved = array(
        'assoc_handle',
        'assoc_type',
        'claimed_id',
        'contact',
        'delegate',
        'dh_consumer_public',
        'dh_gen',
        'dh_modulus',
        'error',
        'identity',
        'invalidate_handle',
        'mode',
        'ns',
        'op_endpoint',
        'openid',
        'realm',
        'reference',
        'response_nonce',
        'return_to',
        'server',
        'session_type',
        'sig',
        'signed',
        'trust_root ',
    );

    /**
     * Whether or not to use namespace alias assignments (for SREG 1.0 mostly)
     * 
     * @var bool
     */
    protected $useNamespaceAlias = true;

    /**
     * Type of message - 'request' or 'response'
     * 
     * @var string
     */
    protected $type = self::REQUEST;

    /**
     * Namespace URI
     * 
     * @see getNamespace()
     * @var string
     */
    protected $namespace = null;

    /**
     * Namespace text, "sreg" or "ax" for example
     * 
     * @var string
     */
    protected $alias = null;

    /**
     * Keys appropriate for a request.  Leave empty to allow any keys.
     * 
     * @var array
     */
    protected $requestKeys = array();

    /**
     * Keys appropriate for a response.  Leave empty to allow any keys.
     * 
     * @var array
     */
    protected $responseKeys = array();

    /**
     * values 
     * 
     * @var array
     */
    protected $values = array();

    /**
     * Sets the message type, request or response
     * 
     * @param string         $type    Type response or type request
     * @param OpenID_Message $message Optional message to get values from
     * 
     * @throws OpenID_Extension_Exception on invalid type argument
     * @return void
     */
    public function __construct($type, OpenID_Message $message = null)
    {
        if ($type != self::REQUEST && $type != self::RESPONSE) {
            throw new OpenID_Extension_Exception('Invalid message type: ' . $type);
        }
        $this->type = $type;

        if ($message !== null) {
            $this->values = $this->fromMessageResponse($message);
        }
    }

    /**
     * Sets a key value pair
     * 
     * @param string $key   Key
     * @param string $value Value
     * 
     * @throws OpenID_Extension_Exception on invalid key argument
     * @return void
     */
    public function set($key, $value)
    {
        $keys = $this->responseKeys;
        if ($this->type == self::REQUEST) {
            $keys = $this->requestKeys;
        }

        if (count($keys) && !in_array($key, $keys)) {
            throw new OpenID_Extension_Exception('Invalid key: ' . $key);
        }
        $this->values[$key] = $value;
    }

    /**
     * Gets a key's value
     * 
     * @param string $key Key
     * 
     * @return mixed Key's value
     */
    public function get($key)
    {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }
        return null;
    }

    /**
     * Adds the extension contents to an OpenID_Message
     * 
     * @param OpenID_Message $message Message to add the extension contents to
     * 
     * @throws OpenID_Extension_Exception on error
     * @return void
     */
    public function toMessage(OpenID_Message $message)
    {
        // Make sure we have a valid alias name
        if (empty($this->alias) || in_array($this->alias, self::$reserved)) {
            throw new OpenID_Extension_Exception(
                'Invalid extension alias' . $this->alias
            );
        }

        $namespaceAlias = 'openid.ns.' . $this->alias;

        // Make sure the alias doesn't collide
        if ($message->get($namespaceAlias) !== null) {
            throw new OpenID_Extension_Exception(
                'Extension alias ' . $this->alias . ' is already set'
            );
        }

        // Add alias assignment? (SREG 1.0 Doesn't use one)
        if ($this->useNamespaceAlias) {
            $message->set($namespaceAlias, $this->namespace);
        }

        foreach ($this->values as $key => $value) {
            $message->set('openid.' . $this->alias . '.' . $key, $value);
        }
    }

    /**
     * Extracts extension contents from an OpenID_Message
     * 
     * @param OpenID_Message $message OpenID_Message to extract the extension 
     *                                contents from
     * 
     * @return array An array of the extension's key/value pairs
     */
    public function fromMessageResponse(OpenID_Message $message)
    {
        $values = array();
        $alias  = null;

        foreach ($message->getArrayFormat() as $ns => $value) {
            if (!preg_match('/^openid[.]ns[.]([^.]*)$/', $ns, $matches)) {
                continue;
            }
            $nsFromMessage = $message->get('openid.ns.' . $matches[1]);
            if ($nsFromMessage !== null && $nsFromMessage != $this->namespace) {
                continue;
            }
            $alias = $matches[1];
        }

        if ($alias === null) {
            return $values;
        }

        if (count($this->responseKeys)) {
            // Only use allowed response keys
            foreach ($this->responseKeys as $key) {
                $value = $message->get('openid.' . $alias . '.' . $key);
                if ($value !== null) {
                    $values[$key] = $value;
                }
            }
        } else {
            // Just grab all message components
            foreach ($message->getArrayFormat() as $key => $value) {
                if (preg_match('/^openid[.]' . $alias . '[.]([^.]+)$/',
                    $key, $matches)) {

                    $values[$matches[1]] = $value;
                }
            }
        }
        return $values;
    }

    /**
     * Gets the namespace of this extension
     * 
     * @see $namespace
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }
}
?>
