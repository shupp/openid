<?php
/**
 * OpenID_Observer_Log 
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

/**
 * Required files
 */
require_once 'OpenID/Observer/Common.php';
require_once 'Log.php';

/**
 * An observer based on PEAR's Log package.  You may either pass in your own Log 
 * instance to the constructor, or allow the default file driver to write to
 * /tmp/OpenID_Observer_Log.log by default.
 *
 * @uses      OpenID_Observer_Common
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_Observer_Log extends OpenID_Observer_Common
{
    /**
     * Holds the instance of Log
     * 
     * @var Log
     */
    protected $log = null;

    /**
     * Allows you to pass in a Log instance and an array of events to log.  If
     * no instance of Log is given, the 'file' Log driver will be used, and write to
     * /tmp/OpenID_Observer_Log.log.
     * 
     * @param Log   $log    Instance of Log, optional
     * @param array $events Custom list of events to log
     * 
     * @return void
     */
    public function __construct(Log $log = null, array $events = array())
    {
        if (count($events)) {
            $this->setEvents($events);
        }

        if (!$log instanceof Log) {
            $log = Log::factory('file', '/tmp/' . __CLASS__ . '.log');
        }
        $this->log = $log;
    }

    /**
     * Logs the event
     * 
     * @param array $event Array containing 'name' and 'data' keys
     * 
     * @return void
     */
    public function update(array $event)
    {
        if (!in_array($event['name'], $this->events)) {
            return;
        }
        $this->log->log($event['name'] . ":\n");
        $this->log->log($event['data']);
    }
}
?>
