<?php

$object = new stdClass;
$object->url = "https://www.google.com/accounts/o8/ud?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.return_to=http%3A%2F%2Flocalhost%3A80%2F%7Ebill%2Fopenid%2Fexamples%2Frelyingparty.php&openid.realm=http%3A%2F%2Flocalhost%3A80%2F&openid.assoc_handle=AOQobUcUm13KWFLw5DKeUcz8HM0ti06YhkipVWWmKNotJ-zkV8JoJ6Le0njZh7Q114wm4eXB&openid.mode=checkid_setup&openid.ns.ui=http%3A%2F%2Fspecs.openid.net%2Fextensions%2Fui%2F1.0&openid.ui.mode=popup&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select";
echo $object->url;

?>
