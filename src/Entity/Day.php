<?php

namespace App\Entity;

use App\Repository\DayRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DayRepository::class)
 */
class Day
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Week::class, inversedBy="days")
     */
    private $week;

    /**
     * @ORM\Column(type="integer")
     */
    public $dailyCount = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWeek(): ?Week
    {
        return $this->week;
    }

    public function setWeek(?Week $week): self
    {
        $this->week = $week;

        return $this;
    }

    public function getDailyCount(): ?int
    {
        return $this->dailyCount;
    }

    public function setDailyCount(int $dailyCount): self
    {
        $this->dailyCount = $dailyCount;

        return $this;
    }
}
