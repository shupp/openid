<?php

error_reporting(E_ALL & ~E_DEPRECATED);

require_once('PEAR/PackageFileManager2.php');

PEAR::setErrorHandling(PEAR_ERROR_DIE);

$packagexml = new PEAR_PackageFileManager2;

$packagexml->setOptions(array(
    'baseinstalldir'    => '/',
    'simpleoutput'      => true,
    'packagedirectory'  => './',
    'filelistgenerator' => 'file',
    'ignore'            => array('phpunit-bootstrap.php', 'phpunit.xml', 'test.php', 'generatePackage.php'),
    'dir_roles' => array(
        'tests'     => 'test',
        'examples'  => 'doc'
    ),
));

$packagexml->setPackage('OpenID');
$packagexml->setSummary('PHP implementation of OpenID 1.1 and 2.0');
$packagexml->setDescription(
    'OpenID is a free and easy way to use a single digital identity across the '
    . 'Internet. See http://openid.net for details.'
);

$packagexml->setChannel('pear.php.net');
$packagexml->setAPIVersion('0.3.3');
$packagexml->setReleaseVersion('0.3.3');

$packagexml->setReleaseStability('alpha');

$packagexml->setAPIStability('alpha');

$packagexml->setNotes('
* Fix bug #19234: Normalization of identifiers/openID not implemented correctly
');
$packagexml->setPackageType('php');
$packagexml->addRelease();

$packagexml->detectDependencies();

$packagexml->addMaintainer('lead',
                           'shupp',
                           'Bill Shupp',
                           'shupp@php.net');
$packagexml->setLicense('New BSD License',
                        'http://www.opensource.org/licenses/bsd-license.php');

$packagexml->setPhpDep('5.1.2');
$packagexml->setPearinstallerDep('1.4.0b1');
$packagexml->addPackageDepWithChannel('required', 'HTTP_Request2', 'pear.php.net', '0.5.1');
$packagexml->addPackageDepWithChannel('required', 'Cache_Lite', 'pear.php.net');
$packagexml->addPackageDepWithChannel('required', 'Crypt_DiffieHellman', 'pear.php.net');
$packagexml->addPackageDepWithChannel('required', 'Services_Yadis', 'pear.php.net', '0.5.1');
$packagexml->addPackageDepWithChannel('optional', 'Log', 'pear.php.net');
$packagexml->addPackageDepWithChannel('required', 'Net_URL2', 'pear.php.net', '0.2.0');
$packagexml->addPackageDepWithChannel('optional', 'MDB2', 'pear.php.net');
$packagexml->addPackageDepWithChannel('optional', 'HTTP_OAuth', 'pear.php.net', '0.1.7');
$packagexml->addExtensionDep('required', 'date');
$packagexml->addExtensionDep('required', 'dom');
$packagexml->addExtensionDep('required', 'hash');
$packagexml->addExtensionDep('required', 'libxml');
$packagexml->addExtensionDep('required', 'mbstring');
$packagexml->addExtensionDep('required', 'pcre');
$packagexml->addExtensionDep('required', 'SPL');


$packagexml->generateContents();
$packagexml->writePackageFile();

?>
