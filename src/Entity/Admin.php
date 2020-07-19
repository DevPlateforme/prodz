<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AdminRepository::class)
 */
class Admin
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Admin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdmin(): ?string
    {
        return $this->Admin;
    }

    public function setAdmin(?string $Admin): self
    {
        $this->Admin = $Admin;

        return $this;
    }
}
