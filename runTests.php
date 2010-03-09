<?php

$base = dirname(__FILE__);
set_include_path(get_include_path() . ":{$base}:{$base}/tests");

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');
PHPUnit_Util_Filter::addFileToWhitelist('OpenID.php');
PHPUnit_Util_Filter::addDirectoryToWhitelist('OpenID');

require_once 'PHPUnit/TextUI/Command.php';

$command = new PHPUnit_TextUI_Command;
$command->run($argv);

?>
