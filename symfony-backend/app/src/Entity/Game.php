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
    private ?Team $localTeam = null;

    #[ORM\ManyToOne(inversedBy: 'awayTeam')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $awayTeam = null;

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

    public function getLocalTeam(): ?Team
    {
        return $this->localTeam;
    }

    public function setLocalTeam(?Team $localTeam): static
    {
        $this->localTeam = $localTeam;

        return $this;
    }

    public function getAwayTeam(): ?Team
    {
        return $this->awayTeam;
    }

    public function setAwayTeam(?Team $awayTeam): static
    {
        $this->awayTeam = $awayTeam;

        return $this;
    }
}
