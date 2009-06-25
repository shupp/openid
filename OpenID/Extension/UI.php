<?php
/**
 * OpenID_Extension_UI 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID_Extension
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
require_once 'OpenID/Extension.php';

/**
 * Provides support for the UI extension
 * 
 * @uses      OpenID_Extension
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class OpenID_Extension_UI extends OpenID_Extension
{
    /**
     * URI of the UI namespace
     * 
     * @var string
     */
    protected $namespace ='http://specs.openid.net/extensions/ui/1.0';

    /**
     * Alias to use
     * 
     * @var string
     */
    protected $alias = 'ui';

    /**
     * Valid modes (only 'popup' so far)
     * 
     * @var array
     */
    protected $validModes = array('popup');

    /**
     * Adds mode checking to set()
     * 
     * @param mixed $key   Key
     * @param mixed $value Value
     * 
     * @return void
     */
    public function set($key, $value)
    {
        if (strpos($key, 'mode') === 0
            && !in_array($value, $this->validModes)) {

            throw new OpenID_Extension_Exception('Invalid UI mode: ' . $key);
        }

        parent::set($key, $value);
    }
}
?>
