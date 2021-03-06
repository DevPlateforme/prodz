<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;



/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity("mail")

 */
class User implements UserInterface
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
    private $userName;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $mail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $passWord;

    private $confirmPassWord;

    /**
     * @ORM\OneToMany(targetEntity=Project::class, mappedBy="user", cascade={"persist", "remove"})
     */
    public $projects;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="user", orphanRemoval=true, cascade={"persist", "remove"})
     */
    public $notifications;

    /**
     * @ORM\Column(type="integer")
     */
    private $pinCount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $associatedMail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $avatar;

    /**
     * @ORM\Column(type="integer")
     */
    private $competencyPoints;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $level;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $avatarAssetSrc;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mailing;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dynamic;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $daysOnTheApp;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $totalWork;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $allDailyCountsDone;

 

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->pinCount = 0;
        $this->competencyPoints = 0;
        $this->level = 'novice';
        $this->setAvatar('man1');
        $this->mailing = 'off';
        $this->daysOnTheApp = 0;
        $this->allDailyCountsDone = 0;
        $this->dynamic = 0;
        $this->totalWork = 0;

        $this->setAvatarAssetSrc('images/man1.png');

    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->userName;
    }

    public function setUsername(string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->passWord;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassWord;
    }

    
    public function setConfirmPassword($confirmPassWord): ?string
    {
        return $this->confirmPassWord = $confirmPassWord;
    }


    public function setPassword(string $passWord): self
    {
        $this->passWord = $passWord;

        return $this;
    }

    public function getRoles(){

        return array('ROLE_USER');

    }

    public function getSalt(){

    }

    public function eraseCredentials(){

    }

    /**
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setUser($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            // set the owning side to null (unless already changed)
            if ($project->getUser() === $this) {
                $project->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(notification $notification): self
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    public function getPinCount(): ?int
    {
        return $this->pinCount;
    }

    public function setPinCount(int $pinCount): self
    {
        $this->pinCount = $pinCount;

        return $this;
    }

    public function getAssociatedMail(): ?string
    {
        return $this->associatedMail;
    }

    public function setAssociatedMail(?string $associatedMail): self
    {
        $this->associatedMail = $associatedMail;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getCompetencyPoints(): ?int
    {
        return $this->competencyPoints;
    }

    public function setCompetencyPoints(int $competencyPoints): self
    {
        $this->competencyPoints = $competencyPoints;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getAvatarAssetSrc(): ?string
    {
        return $this->avatarAssetSrc;
    }

    public function setAvatarAssetSrc(string $avatarAssetSrc): self
    {
        $this->avatarAssetSrc = $avatarAssetSrc;

        return $this;
    }

    public function getMailing(): ?string
    {
        return $this->mailing;
    }

    public function setMailing(string $mailing): self
    {
        $this->mailing = $mailing;

        return $this;
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

    public function getDaysOnTheApp(): ?int
    {
        return $this->daysOnTheApp;
    }

    public function setDaysOnTheApp(?int $daysOnTheApp): self
    {
        $this->daysOnTheApp = $daysOnTheApp;

        return $this;
    }

    public function getTotalWork(): ?int
    {
        return $this->totalWork;
    }

    public function setTotalWork(?int $totalWork): self
    {
        $this->totalWork = $totalWork;

        return $this;
    }

    public function getAllDailyCountsDone(): ?int
    {
        return $this->allDailyCountsDone;
    }

    public function setAllDailyCountsDone(?int $allDailyCountsDone): self
    {
        $this->allDailyCountsDone = $allDailyCountsDone;

        return $this;
    }

   

    


}
