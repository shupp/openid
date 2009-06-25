<?php

interface OpenID_Discover_Interface
{
    public function __construct($identifier);
    public function discover();
}

?>
