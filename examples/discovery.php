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

// A tool for testing discovery

require_once 'common/config.php';

/**
 * getServiceContent 
 * 
 * @param mixed $identifier Identifier
 * @param bool  $skipcache  Whether or not to skip cache
 * 
 * @access public
 * @return void
 */
function getServiceContent($identifier, $skipcache)
{
    $content = null;

    if (!$skipcache) {
        $store = OpenID_Store::getStore();
        $d     = $store->getDiscover($identifier);
        if ($d === false) {
            $d = new OpenID_Discover($identifier);
            try {
                $result = $d->discover();
                if ($result === false) {
                    $content = 'Discovery failed';
                    return $content;
                }
                $store->setDiscover($d);
            } catch (OpenID_Exception $e) {
                return get_class($e) . ': ' . $e->getMessage();
            }
        } else {
            $cache = true;
        }
    } else {
        $d = new OpenID_Discover($identifier);
        try {
            $result = $d->discover();
            if ($result === false) {
                $content = 'Discovery failed';
                return $content;
            }
        } catch (OpenID_Exception $e) {
            return get_class($e) . ': ' . $e->getMessage();
        }
    }

    $content = array();
    if (!empty($cache)) {
        $content['cached'] = true;
    }

    $content['OpenID_Discover'] = $d->services;

    return $content;
}

$identifier = isset($_POST['identifier']) ? $_POST['identifier'] : null;
$skipcache  = isset($_POST['skipcache']) ? $_POST['skipcache'] : null;

if ($identifier) {
    try {
        $identifier = OpenID::normalizeIdentifier($_POST['identifier']);
        $content    = getServiceContent($identifier, $skipcache);
    } catch (OpenID_Exception $e) {
        $content = get_class($e) . ': ' . $e->getMessage();
    }
}

$contents = '';
ob_start();
require_once 'common/discover_form.php';
$contents .= ob_get_contents();
ob_end_clean();

if (isset($content)) {
    $contents .= "<b>Results:</b> <br><pre>\n";
    $contents .= print_r($content, true);
    $contents .= "</pre>\n";
}

require_once 'common/wrapper.php';
?>
