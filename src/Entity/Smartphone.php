<?php

namespace App\Entity;

use App\Repository\SmartphoneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SmartphoneRepository::class)]
class Smartphone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getSmartphones"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getSmartphones"])]
    #[Assert\NotBlank(message: "la marque du téléphone est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "La marque du telephone doit faire au moins {{limit}} caractères", maxMessage: "La marque du telephone ne peut deppaser {{limit}} caractères")]
    private ?string $phone_brand = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getSmartphones"])]
    private ?string $phone_model = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getSmartphones"])]
    private ?string $phone_description = null;

    #[ORM\Column]
    #[Groups(["getSmartphones"])]
    private ?\DateTimeImmutable $phone_created_at = null;

    #[ORM\ManyToOne(inversedBy: 'smartphones', cascade:['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getSmartphones"])]
    private ?Society $Society = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhoneBrand(): ?string
    {
        return $this->phone_brand;
    }

    public function setPhoneBrand(string $phone_brand): static
    {
        $this->phone_brand = $phone_brand;

        return $this;
    }

    public function getPhoneModel(): ?string
    {
        return $this->phone_model;
    }

    public function setPhoneModel(string $phone_model): static
    {
        $this->phone_model = $phone_model;

        return $this;
    }

    public function getPhoneDescription(): ?string
    {
        return $this->phone_description;
    }

    public function setPhoneDescription(string $phone_description): static
    {
        $this->phone_description = $phone_description;

        return $this;
    }

    public function getPhoneCreatedAt(): ?\DateTimeImmutable
    {
        return $this->phone_created_at;
    }

    public function setPhoneCreatedAt(\DateTimeImmutable $phone_created_at): static
    {
        $this->phone_created_at = $phone_created_at;

        return $this;
    }

    public function getSociety(): ?Society
    {
        return $this->Society;
    }

    public function setSociety(?Society $Society): static
    {
        $this->Society = $Society;

        return $this;
    }
}
