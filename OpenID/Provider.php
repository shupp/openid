<?php

require_once 'OpenID.php';

class OpenID_Provider
{
    protected $validOptions = array(
        'associationTypes',
        'Crypt_DiffieHellman',
    );

    public function __construct(OpenID_Message $message,
                                array $options = array())
    {
        $this->setOptions($options);
    }

    protected function setOptions(array $options)
    {
        foreach ($options as $option)
        {
            $this->setOption($option);
        }
    }

    protected function setOption($option)
    {
    }

    /**
     * Processes a request from a Relying Party
     * 
     * @param OpenID_Message $message The OpenID_Message which was sent by the RP
     * 
     * @return bool true on success, false on failure
     */
    public function process(OpenID_Message $message)
    {
    }

    public function getResponse()
    {
    }
}
?>
