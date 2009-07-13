<?php

require_once 'OpenID.php';

class OpenID_Auth_Response
{
    public function __construct(OpenID_Message $message,
                                array $options = array())
    {
    }

    /**
     * Returns the response to an authentication request as an OpenID_Message.
     * 
     * @return OpenID_Message
     */
    public function getResponse()
    {
    }
}
?>
