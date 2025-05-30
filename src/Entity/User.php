<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert; 


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
class User implements \Symfony\Component\Security\Core\User\UserInterface
{

     #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column('password')]
    #[Assert\NotBlank(message: 'Não pode estar vazio')]
    #[Assert\Length(min: 8)]
    #[Assert\NotNull()]
    private string $password;

    #[ORM\Column('email', unique: true)]
    #[Assert\NotBlank(message: 'Não pode estar vazio')]
    #[Assert\NotNull()]
    #[Assert\Email()]
    private string $email;
    #[ORM\Column('name')]
    #[Assert\NotBlank(message: 'Não pode estar vazio')]
    #[Assert\Length(min: 3)]
    #[Assert\NotNull()]
    private string $name;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: UserToken::class, mappedBy: 'tokens')]
    private Collection $tokens;

    public function getTokens() 
    {
        $this->tokens;
    }

    public function addToken(UserToken $token)
    {
        $this->tokens->add($token);
        return $this;
    }

    public function setPassword(string $password)
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getRoles(): array
    {
        return [
            'ROLE_USER'
        ];
    }

    public function __serialize()
    {
        return [
            'id' => $this->getId(),
            'email' => $this->email,
            'name' => $this->name
        ];
    }
    public function eraseCredentials(): void
    {
        unset($this->password);
    }

    public function checkPassword(string $password): bool
    {
        return password_verify(
            $password,
            $this->password
        );
    }

    /**
     * Returns the identifier for this user (e.g. username or email address).
     *
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable(); // Também define updated_at na criação
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setName(string $name)
    {
        $this->name  =$name;
        return $this;
    }
    public function getName()
    {
        return $this->name;
    }
     public function setEmail(string $email)
    {
        $this->email = $email;
        return $this;
    }
    public function getEmail()
    {
        return $this->email;
    }
}
