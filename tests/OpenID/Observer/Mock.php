<?php
/**
 * OpenID_Observer_Mock 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      OpenID_Observer_Common
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */

require_once 'OpenID/Observer/Common.php';
require_once 'OpenID/Exception.php';

/**
 * OpenID_Observer_Mock 
 * 
 * @uses      OpenID_Observer_Common
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_Observer_Mock extends OpenID_Observer_Common
{
    /**
     * events 
     * 
     * @var array
     */
    protected $events = array('foo');

    /**
     * update 
     * 
     * @param array $event event
     * 
     * @return void
     */
    public function update(array $event)
    {
        throw new OpenID_Exception('test');
    }
}
?>
