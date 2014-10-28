<?php

class UserService
{
    /** @var string */
    public $key;

    /** @var int number of logins performed */
    public $num_logins = 0;

    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function login($username, $password)
    {
        $this->num_logins += 1;

        return $this->key == 'abc123' ? true : false;
    }
}
