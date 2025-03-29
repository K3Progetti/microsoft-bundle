<?php

namespace K3Progetti\MicrosoftBundle\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_microsoft_data')]
class UserMicrosoftData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'microsoftData')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $microsoftId = null;

    public function getId(): int
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
