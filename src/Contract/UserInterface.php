<?php

namespace K3Progetti\MicrosoftBundle\Contract;

interface UserInterface
{
    public function getId(): mixed;
    public function setUsername(string $username): static;
    public function setEmail(string $email): static;
    public function setActive(bool $active): static;
    public function setPassword(string $password): static;
    public function setSurname(?string $surname): static;
    public function setName(?string $name): static;
    public function setPhone(?string $phone): static;
    public function setRoles(array $roles): static;
}
