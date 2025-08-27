<?php

namespace App\Entity;

use App\Repository\DatasetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DatasetRepository::class)]
class Dataset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 50)]
    private ?string $fileType = null;

    #[ORM\Column]
    private ?int $fileSize = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $uploadedAt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 4, nullable: true)]
    private ?string $mktValue = null;

    #[ORM\Column]
    private ?int $temperatureReadingsCount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 4, nullable: true)]
    private ?string $minTemperature = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 4, nullable: true)]
    private ?string $maxTemperature = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 4, nullable: true)]
    private ?string $avgTemperature = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endTime = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 4)]
    private ?string $activationEnergy = '83.144';

    #[ORM\OneToMany(mappedBy: 'dataset', targetEntity: TemperatureReading::class, orphanRemoval: true)]
    private Collection $temperatureReadings;

    public function __construct()
    {
        $this->temperatureReadings = new ArrayCollection();
        $this->uploadedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;
        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(string $fileType): static
    {
        $this->fileType = $fileType;
        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): static
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;
        return $this;
    }

    public function getMktValue(): ?string
    {
        return $this->mktValue;
    }

    public function setMktValue(?string $mktValue): static
    {
        $this->mktValue = $mktValue;
        return $this;
    }

    public function getTemperatureReadingsCount(): ?int
    {
        return $this->temperatureReadingsCount;
    }

    public function setTemperatureReadingsCount(int $temperatureReadingsCount): static
    {
        $this->temperatureReadingsCount = $temperatureReadingsCount;
        return $this;
    }

    public function getMinTemperature(): ?string
    {
        return $this->minTemperature;
    }

    public function setMinTemperature(?string $minTemperature): static
    {
        $this->minTemperature = $minTemperature;
        return $this;
    }

    public function getMaxTemperature(): ?string
    {
        return $this->maxTemperature;
    }

    public function setMaxTemperature(?string $maxTemperature): static
    {
        $this->maxTemperature = $maxTemperature;
        return $this;
    }

    public function getAvgTemperature(): ?string
    {
        return $this->avgTemperature;
    }

    public function setAvgTemperature(?string $avgTemperature): static
    {
        $this->avgTemperature = $avgTemperature;
        return $this;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeImmutable $endTime): static
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getActivationEnergy(): ?string
    {
        return $this->activationEnergy;
    }

    public function setActivationEnergy(string $activationEnergy): static
    {
        $this->activationEnergy = $activationEnergy;
        return $this;
    }

    /**
     * @return Collection<int, TemperatureReading>
     */
    public function getTemperatureReadings(): Collection
    {
        return $this->temperatureReadings;
    }

    public function addTemperatureReading(TemperatureReading $temperatureReading): static
    {
        if (!$this->temperatureReadings->contains($temperatureReading)) {
            $this->temperatureReadings->add($temperatureReading);
            $temperatureReading->setDataset($this);
        }

        return $this;
    }

    public function removeTemperatureReading(TemperatureReading $temperatureReading): static
    {
        if ($this->temperatureReadings->removeElement($temperatureReading)) {
            // set the owning side to null (unless already changed)
            if ($temperatureReading->getDataset() === $this) {
                $temperatureReading->setDataset(null);
            }
        }

        return $this;
    }
}
