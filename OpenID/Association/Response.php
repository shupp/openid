<?php

require_once 'OpenID.php';

class OpenID_Association_Response
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

    public function getResponse()
    {
    }
}
?>
