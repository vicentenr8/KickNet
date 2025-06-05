<?php

namespace App\Entity;

use App\Repository\ForecastRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ForecastRepository::class)]
class Forecast
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1)]
    private ?string $result = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $forecastDate = null;

    #[ORM\ManyToOne(inversedBy: 'forecasts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $users = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\Column(type: "integer", nullable: false)]
    private $externalGameId;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(string $result): static
    {
        $this->result = $result;

        return $this;
    }

    public function getForecastDate(): ?\DateTimeInterface
    {
        return $this->forecastDate;
    }

    public function setForecastDate(\DateTimeInterface $forecastDate): static
    {
        $this->forecastDate = $forecastDate;

        return $this;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): static
    {
        $this->users = $users;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }
    public function setGame(?Game $game): static
    {
        $this->game = $game;

        return $this;
    }

    public function getExternalGameId(): ?int
    {
        return $this->externalGameId;
    }

    public function setExternalGameId(int $externalGameId): self
    {
        $this->externalGameId = $externalGameId;

        return $this;
    }
}
