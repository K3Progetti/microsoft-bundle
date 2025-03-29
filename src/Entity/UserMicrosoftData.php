<?php

namespace K3Progetti\MicrosoftBundle\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use K3Progetti\MicrosoftBundle\Repository\UserMicrosoftDataRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserMicrosoftDataRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'user_microsoft_data')]
class UserMicrosoftData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['user_microsoft'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'microsoftData', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotBlank]
    #[Groups(['with_user'])]
    private User $user;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Groups(['user_microsoft'])]
    private ?string $microsoftId = null;

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
}
