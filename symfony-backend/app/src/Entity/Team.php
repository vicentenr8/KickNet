<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $teamName = null;

    #[ORM\Column(length: 50)]
    private ?string $country = null;

    #[ORM\Column(length: 50)]
    private ?string $league = null;

    /**
     * @var Collection<int, Game>
     */
  
    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: 'localTeam')]
    private Collection $localGames;

    /**
     * @var Collection<int, Game>
     */
  
    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: 'awayTeam')]
    private Collection $awayGames;

    public function __construct()
    {
        
        $this->localGames = new ArrayCollection();
        $this->awayGames = new ArrayCollection(); 
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeamName(): ?string
    {
        return $this->teamName;
    }

    public function setTeamName(string $teamName): static
    {
        $this->teamName = $teamName;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getLeague(): ?string
    {
        return $this->league;
    }

    public function setLeague(string $league): static
    {
        $this->league = $league;

        return $this;
    }

    /**
     * @return Collection<int, Game>
     */


    public function getLocalGames(): Collection
    {
        return $this->localGames;
    }


    public function addLocalGame(Game $localGame): static
    {
        if (!$this->localGames->contains($localGame)) {
            $this->localGames->add($localGame);
            $localGame->setLocalTeam($this);
        }

        return $this;
    }

    public function removeLocalGame(Game $localGame): static
    {
        if ($this->localGames->removeElement($localGame)) {
            
            if ($localGame->getLocalTeam() === $this) {
                $localGame->setLocalTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Game>
     */

    public function getAwayGames(): Collection
    {
        return $this->awayGames;
    }

    public function addAwayGame(Game $awayGame): static
    {
        if (!$this->awayGames->contains($awayGame)) {
            $this->awayGames->add($awayGame);
           
            $awayGame->setAwayTeam($this);
        }

        return $this;
    }

   
    public function removeAwayGame(Game $awayGame): static
    {
        if ($this->awayGames->removeElement($awayGame)) {
        
            if ($awayGame->getAwayTeam() === $this) {
                $awayGame->setAwayTeam(null);
            }
        }

        return $this;
    }
}