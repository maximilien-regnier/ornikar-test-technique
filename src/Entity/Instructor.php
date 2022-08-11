<?php

namespace App\Entity;


class Instructor
{
    public int $id;
    public string $firstname;
    public string $lastname;

    public function __construct(int $id, string $firstname, string $lastname)
    {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
    }
}
