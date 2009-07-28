<?php

require_once 'OpenID.php';

class OpenID_Provider
{
    protected $message = null;
    protected $options = array();
    protected $validModes = array(
        'checkid_setup',
        'associate',
        'checkid_authenticate'
    );
    protected $validOptions = array(
        'associationTypes',
        'Crypt_DiffieHellman',
    );

    public function __construct(OpenID_Message $message,
                                array $options = array())
    {
        $this->message = $message;
        $this->setOptions($options);
    }

    protected function setOptions(array $options)
    {
        foreach ($options as $name => $value)
        {
            $this->setOption($name, $value);
        }
    }

    protected function setOption($name, $value)
    {
        if (in_array($name, $this->validOptions)) {
            $this->options[$name] = $value;
        }
    }

    public function process()
    {
        switch ($this->message->get('openid.mode')) {
        case 'associate':
           $this->processAssociate();
        case 'checkid_setup':
           $this->processCheckIDSetup();
        case 'checkid_authenticate':
           $this->processCheckIDAuthenticate();
        default:
            throw new OpenID_Provider_Exception(
                'Invalid mode'
            );
        }
    }

    public function getResponse()
    {
    }
}
?>
