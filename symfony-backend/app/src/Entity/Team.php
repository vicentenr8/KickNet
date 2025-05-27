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
    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: 'team')]
    private Collection $localTeam;

    /**
     * @var Collection<int, Game>
     */
    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: 'teams')]
    private Collection $awayTeam;

    public function __construct()
    {
        $this->localTeam = new ArrayCollection();
        $this->awayTeam = new ArrayCollection();
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
    public function getLocalTeam(): Collection
    {
        return $this->localTeam;
    }

    public function addLocalTeam(Game $localTeam): static
    {
        if (!$this->localTeam->contains($localTeam)) {
            $this->localTeam->add($localTeam);
            $localTeam->setTeam($this);
        }

        return $this;
    }

    public function removeLocalTeam(Game $localTeam): static
    {
        if ($this->localTeam->removeElement($localTeam)) {
            // set the owning side to null (unless already changed)
            if ($localTeam->getTeam() === $this) {
                $localTeam->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getAwayTeam(): Collection
    {
        return $this->awayTeam;
    }

    public function addAwayTeam(Game $awayTeam): static
    {
        if (!$this->awayTeam->contains($awayTeam)) {
            $this->awayTeam->add($awayTeam);
            $awayTeam->setTeams($this);
        }

        return $this;
    }

    public function removeAwayTeam(Game $awayTeam): static
    {
        if ($this->awayTeam->removeElement($awayTeam)) {
            // set the owning side to null (unless already changed)
            if ($awayTeam->getTeams() === $this) {
                $awayTeam->setTeams(null);
            }
        }

        return $this;
    }
}
