<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $gameDate = null;

    #[ORM\Column(length: 60)]
    private ?string $competition = null;

    #[ORM\ManyToOne(inversedBy: 'localTeam')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $team = null;

    #[ORM\ManyToOne(inversedBy: 'awayTeam')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $teams = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGameDate(): ?\DateTimeInterface
    {
        return $this->gameDate;
    }

    public function setGameDate(\DateTimeInterface $gameDate): static
    {
        $this->gameDate = $gameDate;

        return $this;
    }

    public function getCompetition(): ?string
    {
        return $this->competition;
    }

    public function setCompetition(string $competition): static
    {
        $this->competition = $competition;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getTeams(): ?Team
    {
        return $this->teams;
    }

    public function setTeams(?Team $teams): static
    {
        $this->teams = $teams;

        return $this;
    }
}
