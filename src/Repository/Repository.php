<?php

namespace App\Repository;


interface Repository
{
    public function getById($id);

    public function save($entity);
}