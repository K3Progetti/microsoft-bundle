<?php

namespace K3Progetti\MicrosoftBundle\Entity;

use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use K3Progetti\MicrosoftBundle\Repository\MicrosoftUserRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MicrosoftUserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'app_user_microsoft')]
class MicrosoftUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['microsoft_user'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'microsoftUser', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotBlank]
    #[Groups(['microsoft_user'])]
    private User $user;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Groups(['microsoft_user'])]
    private ?string $microsoftId = null;


    #[ORM\OneToMany(mappedBy: 'user', targetEntity: MicrosoftGroupUser::class)]
    private Collection $microsoftGroupUsers;


    public function __construct()
    {
        $this->microsoftGroupUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getMicrosoftId(): ?string
    {
        return $this->microsoftId;
    }

    public function setMicrosoftId(?string $microsoftId): self
    {
        $this->microsoftId = $microsoftId;
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
            $microsoftGroupUser->setUser($this);
        }

        return $this;
    }

    public function removeMicrosoftGroupUsers(MicrosoftGroupUser $microsoftGroupUser): static
    {
        if ($this->microsoftGroupUsers->removeElement($microsoftGroupUser)) {
            // set the owning side to null (unless already changed)
            if ($microsoftGroupUser->getGroup() === $this) {
                $microsoftGroupUser->setUser(null);
            }
        }

        return $this;
    }
}
