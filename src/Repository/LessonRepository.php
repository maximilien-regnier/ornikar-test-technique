<?php
namespace App\Repository;


use App\Entity\Lesson;
use App\Helper\SingletonTrait;

// DO NOT MODIFY THIS CLASS
class LessonRepository implements Repository
{
    use SingletonTrait;

    private $siteId;
    private $instructorId;
    private $date;
    private $start_at;
    private $end_at;
    private array $base;

    /**
     * LessonRepository constructor.
     */
    public function __construct()
    {
        $this->base = [];
    }

    /**
     * @param int $id
     *
     * @return Lesson
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
