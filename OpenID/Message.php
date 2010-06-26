<?php
/**
 * OpenID_Message 
 * 
 * PHP Version 5.2.0+
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */

/**
 * Required files
 */
require_once 'OpenID.php';
require_once 'OpenID/Message/Exception.php';

/**
 * OpenID_Message 
 * 
 * A class that handles any OpenID protocol messages, as described in section 4.1 of
 * the {@link http://openid.net/specs/openid-authentication-2_0.html#anchor4 
 * OpenID 2.0 spec}.  You can set or get messages in one of 3 formats:  Key Value 
 * (KV), Array, or HTTP.  KV is described in the spec (4.1.1 of the 2.0 spec), HTTP 
 * is urlencoded key value pairs, as you would see them in a query string or an HTTP
 * POST body.
 * 
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_Message
{
    const FORMAT_KV    = 'KV';
    const FORMAT_HTTP  = 'HTTP';
    const FORMAT_ARRAY = 'ARRAY';

    protected $validFormats = array(self::FORMAT_KV,
                                    self::FORMAT_HTTP,
                                    self::FORMAT_ARRAY);

    protected $data = array();

    /**
     * Optionally instanciates this object with the contents of an OpenID message.
     * 
     * @param mixed  $message Message contents
     * @param string $format  Source message format (KV, HTTP, or ARRAY)
     * 
     * @return void
     */
    public function __construct($message = null, $format = self::FORMAT_ARRAY)
    {
        if ($message !== null) {
            $this->setMessage($message, $format);
        }
    }

    /**
     * Gets the value of any key in this message.
     * 
     * @param string $name Name of key
     * 
     * @return mixed Value of key if set, defaults to null
     */
    public function get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return null;
    }

    /**
     * Sets a message key value.
     * 
     * @param string $name Key name
     * @param mixed  $val  Key value
     * 
     * @return void
     */
    public function set($name, $val)
    {
        if ($name == 'openid.ns' && $val != OpenID::NS_2_0) {
            throw new OpenID_Message_Exception('Invalid openid.ns value: ' . $val);
        }
        $this->data[$name] = $val;
    }

    /**
     * Deletes a key from a message
     * 
     * @param string $name Key name
     * 
     * @return void
     */
    public function delete($name)
    {
        unset($this->data[$name]);
    }

    /**
     * Gets the current message in KV format
     * 
     * @return string
     * @see getMessage()
     */
    public function getKVFormat()
    {
        return $this->getMessage(self::FORMAT_KV);
    }

    /**
     * Gets the current message in HTTP (url encoded) format
     * 
     * @return string
     * @see getMessage()
     */
    public function getHTTPFormat()
    {
        return $this->getMessage(self::FORMAT_HTTP);
    }

    /**
     * Gets the current message in ARRAY format
     * 
     * @return array
     * @see getMessage()
     */
    public function getArrayFormat()
    {
        return $this->getMessage(self::FORMAT_ARRAY);
    }

    /**
     * Gets the message in one of three formats:
     * 
     *  OpenID_Message::FORMAT_ARRAY (default)
     *  OpenID_Message::FORMAT_KV (KV pairs, OpenID response format)
     *  OpenID_Message::FORMAT_HTTP (url encoded pairs, for use in a query string)
     * 
     * @param string $format One of the above three formats
     * 
     * @throws OpenID_Message_Exception When passed an invalid format argument
     * @return mixed array, kv string, or url query string paramters
     */
    public function getMessage($format = self::FORMAT_ARRAY)
    {
        if ($format === self::FORMAT_ARRAY) {
            return $this->data;
        }

        if ($format === self::FORMAT_HTTP) {
            foreach ($this->data as $k => $v) {
                $pairs[] = urlencode($k) . '=' . urlencode($v);
            }
            return implode('&', $pairs);
        }

        if ($format === self::FORMAT_KV) {
            $message = '';
            foreach ($this->data as $k => $v) {
                $message .= "$k:$v\n";
            }
            return $message;
        }

        throw new OpenID_Message_Exception('Invalid format: ' . $format);
    }

    /**
     * Sets message contents.  Wipes out any existing message contents.  Default 
     * source format is Array, but you can also use KV and HTTP formats.
     * 
     * @param mixed $message Source message
     * @param mixed $format  Source message format (OpenID_Message::FORMAT_KV,
     *                                              OpenID_Message::FORMAT_ARRAY,
     *                                              OpenID_Message::FORMAT_HTTP)
     * 
     * @return void
     */
    public function setMessage($message, $format = self::FORMAT_ARRAY)
    {
        if (!in_array($format, $this->validFormats)) {
            throw new OpenID_Message_Exception('Invalid format: ' . $format);
        }

        // Flush current data
        $this->data = array();

        if ($format == self::FORMAT_ARRAY) {
            foreach ($message as $k => $v) {
                $this->set($k, $v);
            }
            return;
        }

        if ($format == self::FORMAT_KV) {
            $lines = explode("\n", $message);
            foreach ($lines as $line) {
                if ($line != '') {
                        list($key, $value) = explode(':', $line, 2);
                        $this->set($key, $value);
                }
            }
            return;
        }

        if ($format == self::FORMAT_HTTP) {
            $array = explode('&', $message);
            foreach ($array as $pair) {
                $parts = explode('=', $pair, 2);
                if (count($parts) < 2) {
                    continue;
                }
                $this->set(urldecode($parts[0]), urldecode($parts[1]));
            }
        }
    }

    /**
     * Adds an extension to an OpenID_Message object.
     * 
     * @param OpenID_Extension $extension Instance of OpenID_Extension
     * 
     * @see OpenID_Extension
     * @return void
     */
    public function addExtension(OpenID_Extension $extension)
    {
        $extension->toMessage($this);
    }
}
?>
