<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SocietyRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SocietyRepository::class)]
class Society
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getSmartphones"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getSmartphones"])]
    private ?string $society_name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getSmartphones"])]
    private ?string $society_description = null;

    #[ORM\OneToMany(mappedBy: 'Society', targetEntity: Smartphone::class, orphanRemoval: true)]
    private Collection $smartphones;

    public function __construct()
    {
        $this->smartphones = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSocietyName(): ?string
    {
        return $this->society_name;
    }

    public function setSocietyName(string $society_name): static
    {
        $this->society_name = $society_name;

        return $this;
    }

    public function getSocietyDescription(): ?string
    {
        return $this->society_description;
    }

    public function setSocietyDescription(string $society_description): static
    {
        $this->society_description = $society_description;

        return $this;
    }

    /**
     * @return Collection<int, Smartphone>
     */
    public function getSmartphones(): Collection
    {
        return $this->smartphones;
    }

    public function addSmartphone(Smartphone $smartphone): static
    {
        if (!$this->smartphones->contains($smartphone)) {
            $this->smartphones->add($smartphone);
            $smartphone->setSociety($this);
        }

        return $this;
    }

    public function removeSmartphone(Smartphone $smartphone): static
    {
        if ($this->smartphones->removeElement($smartphone)) {
            // set the owning side to null (unless already changed)
            if ($smartphone->getSociety() === $this) {
                $smartphone->setSociety(null);
            }
        }

        return $this;
    }
}
