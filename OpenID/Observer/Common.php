<?php
/**
 * OpenID_Observer_Common 
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
 * Allows for observers to listen in to key events.  The most common use case is for
 * logging.  To use OpenID_Observe_Log, for example you could do this:
 *
 * <code>
 *  $log = new OpenID_Observer_Log;
 *  OpenID::attach($log);
 * </code>
 *
 * Now, your logs will by default go to /tmp/OpenID_Observer_Log.log.  To stop 
 * observing, just detach like so:
 * 
 * <code>
 *  OpenID::detach($log);
 * </code>
 *
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 * @see       OpenID_Observer_Log
 */
abstract class OpenID_Observer_Common
{
    /**
     * Events to act upon
     * 
     * @var array
     * @see getEvents()
     */
    protected $events = array(
        'OpenID_Association_Request::sendAssociationRequest',
        'OpenID_Assertion::validateReturnToNonce',
        'OpenID_Auth_Request::addNonce',
    );

    /**
     * Gets the current array of events
     * 
     * @return array
     * @see $events
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Sets a custom array of events to act upon
     * 
     * @param array $events Array of events
     * 
     * @return void
     * @see $events
     */
    public function setEvents(array $events)
    {
        $this->events = $events;
    }

    /**
     * Acts upon an event that just occured
     * 
     * @param array $event Event array, containing 'name' and 'data' keys
     * 
     * @return void
     */
    abstract public function update(array $event);
}
?>
