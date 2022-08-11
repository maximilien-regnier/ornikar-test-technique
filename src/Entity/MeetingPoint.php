<?php

namespace App\Entity;

class MeetingPoint
{
    public int $id;
    public string $url;
    public string $name;

    public function __construct(int $id, string $url, string $name)
    {
        $this->id = $id;
        $this->url = $url;
        $this->name = $name;
    }
}
