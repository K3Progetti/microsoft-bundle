<?php

namespace K3Progetti\MicrosoftBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use K3Progetti\MicrosoftBundle\Repository\MicrosoftUserRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MicrosoftUserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'microsoft_group_user')]
class MicrosoftGroupUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['user_microsoft'])]
    private ?int $id = null;


    #[ORM\ManyToOne(inversedBy: 'microsoftGroupUsers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotBlank]
    #[Groups(['with_microsoft_group'])]
    private ?MicrosoftGroup $group = null;

    #[ORM\ManyToOne(inversedBy: 'microsoftGroupUser')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotBlank]
    #[Groups(['with_microsoft_group'])]
    private ?MicrosoftUser $user = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): MicrosoftUser
    {
        return $this->user;
    }

    public function setUser(?MicrosoftUser $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getGroup(): MicrosoftGroup
    {
        return $this->group;
    }

    public function setGroup(?MicrosoftGroup $group): static
    {
        $this->group = $group;

        return $this;
    }

}
