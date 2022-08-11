<?php

namespace App\Entity;

class Learner
{
    public int $id;
    public string $firstname;
    public string $lastname;
    public string $email;

    public function __construct(int $id, string $firstname, string $lastname, string $email)
    {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
    }
}
