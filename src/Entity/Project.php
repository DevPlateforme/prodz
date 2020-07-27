<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 *  @UniqueEntity("projectName")
 */
class Project
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $projectName;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="projects")
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalLimit;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dailyLimit;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $totalCount;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dailyCount;

    /**
     * @ORM\Column(type="string")
     */
    private $dailyCountDone;

    /**
     * @ORM\Column(type="string")
     */
    private $totalCountDone;


     function __construct(){
        $this->dailyCount = 0;

        $this->totalCount = 0;

        $this->dailyCountDone = 'false';

        $this->totalCountDone = 'false';

    }

  
    public function getProjectName(): ?string
    {
        return $this->projectName;
    }

    public function setProjectName(string $projectName): self
    {
        $this->projectName = $projectName;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTotalLimit(): ?int
    {
        return $this->totalLimit;
    }

    public function setTotalLimit(string $totalLimit): self
    {

        if($totalLimit == 'ponctual'){ 

            $this->totalLimit = 36000;



        } else if($totalLimit == 'midTerm'){

            $this->totalLimit = 180000;


        } else if($totalLimit == 'longTerm'){

            $this->totalLimit = 360000;

        }


        return $this;
    }

    public function getDailyLimit(): ?int
    {
        return $this->dailyLimit;
    }

    public function setDailyLimit(string $dailyLimit): self
    {
        if($dailyLimit == 'dailyLimit1'){

            $this->dailyLimit = 10;
        } elseif( $dailyLimit == 'dailyLimit2'){
 
            $this->dailyLimit = 30;

        } elseif( $dailyLimit == 'dailyLimit3'){

            $this->dailyLimit = 60;
        }

        return $this;
    }




    public function getDailyCount(){

        return $this->dailyCount;

    }

    public function getTotalCount(){

        return $this->totalCount;

    }







    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDailyCountDone()
    {
        return $this->dailyCountDone;
    }

    public function setDailyCountToDone()
    {
        $this->dailyCountDone = 'true';

        return $this;
    }

    public function getTotalCountDone()
    {
        return $this->totalCountDone;
    }

    public function setTotalCountToDone()
    {
        $this->totalCountDone = 'true';

        return $this;
    }
}
