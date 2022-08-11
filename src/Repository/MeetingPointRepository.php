<?php
namespace App\Repository;


use App\Entity\MeetingPoint;
use App\Helper\SingletonTrait;

// DO NOT MODIFY THIS CLASS
class MeetingPointRepository implements Repository
{
    use SingletonTrait;

    private array $base;

    /**
     * MeetingPointRepository constructor.
     */
    public function __construct()
    {
        $this->base = [];
    }


    /**
     * @param int $id
     *
     * @return MeetingPoint
     */
    public function getById($id)
    {
        return $this->base[$id];
    }

    public function save($entity)
    {
        $this->base[$entity->id] = $entity;
    }
}
