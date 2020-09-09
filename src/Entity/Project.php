<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
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
     * @ORM\Column(type="string", length=255)
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
    public $totalCount = 0;

    /**
     * @ORM\Column(type="string")
     */
    private $dailyCountDone;

    /**
     * @ORM\Column(type="string")
     */
    private $totalCountDone;

    /**
     * @ORM\Column(type="integer")
     */
    public $currentDay = 0;

    /**
     * @ORM\Column(type="integer")
     */
    public $currentWeek = 0;

    /**
     * @ORM\OneToMany(targetEntity=Week::class, mappedBy="project", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    public $weeks;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dynamic;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $daysFromCreation;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dailyCountsDoneCount;


     function __construct(){

        $this->weeks = new ArrayCollection();
         
        $this->dailyCount = 0;

        $this->totalCount = 0;

        $this->dailyCountDone = 'false';

        $this->totalCountDone = 'false';

        $this->dynamic = 0;

        $this->daysFromCreation = 0;

        $this->dailyCountsDoneCount = 0;


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

            $this->dailyLimit = 600;
        } elseif( $dailyLimit == 'dailyLimit2'){
 
            $this->dailyLimit = 1800;

        } elseif( $dailyLimit == 'dailyLimit3'){

            $this->dailyLimit = 3600;
        }

        return $this;
    }




    public function getDailyCount(){

        return $this->dailyCount;

    }

    public function getTotalCount(){

        return $this->totalCount;

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

    public function initializeDailyCount()
    {
        $this->dailyCountDone = 'false';

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

    public function getCurrentDay(): ?int
    {
        return $this->currentDay;
    }

    public function setCurrentDay(int $currentDay): self
    {
        $this->currentDay = $currentDay;

        return $this;
    }

    public function getCurrentWeek(): ?int
    {
        return $this->currentWeek;
    }

    public function setCurrentWeek(int $currentWeek): self
    {
        $this->currentWeek = $currentWeek;

        return $this;
    }

    /**
     * @return Collection|Week[]
     */
    public function getWeeks(): Collection
    {
        return $this->weeks;
    }

    public function addWeek(Week $week): self
    {
        if (!$this->weeks->contains($week)) {
            $this->weeks[] = $week;
            $week->setProject($this);
        }

        return $this;
    }

    public function removeWeek(Week $week): self
    {
        if ($this->weeks->contains($week)) {
            $this->weeks->removeElement($week);
            // set the owning side to null (unless already changed)
            if ($week->getProject() === $this) {
                $week->setProject(null);
            }
        }

        return $this;
    }


    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDynamic(): ?int
    {
        return $this->dynamic;
    }

    public function setDynamic(?int $dynamic): self
    {
        $this->dynamic = $dynamic;

        return $this;
    }

    public function getDaysFromCreation(): ?int
    {
        return $this->daysFromCreation;
    }

    public function setDaysFromCreation(?int $daysFromCreation): self
    {
        $this->daysFromCreation = $daysFromCreation;

        return $this;
    }

    public function getDailyCountsDoneCount(): ?int
    {
        return $this->dailyCountsDoneCount;
    }

    public function setDailyCountsDoneCount(?int $dailyCountsDoneCount): self
    {
        $this->dailyCountsDoneCount = $dailyCountsDoneCount;

        return $this;
    }
}
