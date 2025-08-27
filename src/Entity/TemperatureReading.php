<?php

namespace App\Entity;

use App\Repository\TemperatureReadingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TemperatureReadingRepository::class)]
class TemperatureReading
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'temperatureReadings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Dataset $dataset = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $timestamp = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 4)]
    private ?string $temperature = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 4, nullable: true)]
    private ?string $temperatureKelvin = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $unixTimestamp = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $originalData = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDataset(): ?Dataset
    {
        return $this->dataset;
    }

    public function setDataset(?Dataset $dataset): static
    {
        $this->dataset = $dataset;
        return $this;
    }

    public function getTimestamp(): ?\DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeImmutable $timestamp): static
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function getTemperature(): ?string
    {
        return $this->temperature;
    }

    public function setTemperature(string $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function getTemperatureKelvin(): ?string
    {
        return $this->temperatureKelvin;
    }

    public function setTemperatureKelvin(?string $temperatureKelvin): static
    {
        $this->temperatureKelvin = $temperatureKelvin;
        return $this;
    }

    public function getUnixTimestamp(): ?int
    {
        return $this->unixTimestamp;
    }

    public function setUnixTimestamp(?int $unixTimestamp): static
    {
        $this->unixTimestamp = $unixTimestamp;
        return $this;
    }

    public function getOriginalData(): ?string
    {
        return $this->originalData;
    }

    public function setOriginalData(?string $originalData): static
    {
        $this->originalData = $originalData;
        return $this;
    }
}
