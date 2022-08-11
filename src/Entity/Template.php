<?php

namespace App\Entity;

class Template
{
    public int $id;
    public string $subject;
    public string $content;

    public function __construct(int $id, string $subject, string $content)
    {
        $this->id = $id;
        $this->subject = $subject;
        $this->content = $content;
    }
}