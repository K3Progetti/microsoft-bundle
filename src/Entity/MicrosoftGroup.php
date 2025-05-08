<?php

namespace K3Progetti\MicrosoftBundle\Entity;

use App\Entity\MicrosoftGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use K3Progetti\MicrosoftBundle\Repository\MicrosoftUserRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MicrosoftUserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'microsoft_groups')]
class MicrosoftGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['microsoft_group'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Groups(['microsoft_group'])]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Groups(['microsoft_group'])]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Groups(['microsoft_group'])]
    private ?string $microsoftGroupId = null;

    #[ORM\OneToMany(mappedBy: 'group', targetEntity: MicrosoftGroupUser::class)]
    private Collection $microsoftGroupUsers;

    public function __construct()
    {
        $this->microsoftGroupUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getMicrosoftGroupId(): ?string
    {
        return $this->microsoftGroupId;
    }

    public function setMicrosoftGroupId(?string $microsoftGroupId): static
    {
        $this->microsoftGroupId = $microsoftGroupId;

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

    /**
     * @return Collection<int, MicrosoftGroup>
     */
    public function getMicrosoftGroupUsers(): Collection
    {
        return $this->microsoftGroupUsers;
    }

    public function addMicrosoftGroupUser(MicrosoftGroupUser $microsoftGroupUser): static
    {
        if (!$this->microsoftGroupUsers->contains($microsoftGroupUser)) {
            $this->microsoftGroupUsers->add($microsoftGroupUser);
            $microsoftGroupUser->setGroup($this);
        }

        return $this;
    }

    public function removeMicrosoftGroupUsers(MicrosoftGroupUser $microsoftGroupUser): static
    {
        if ($this->microsoftGroupUsers->removeElement($microsoftGroupUser)) {
            // set the owning side to null (unless already changed)
            if ($microsoftGroupUser->getGroup() === $this) {
                $microsoftGroupUser->setGroup(null);
            }
        }

        return $this;
    }
}
