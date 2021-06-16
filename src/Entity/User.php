<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    public const STATUS_IDLE    = 'idle';
    public const STATUS_RUNNING = 'running';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $accessToken;

    /**
     * @ORM\Column(type="bigint", unique=true)
     */
    private int $githubId;

    private array $data;

    /**
     * @ORM\OneToMany(targetEntity=UserLanguage::class, mappedBy="user", orphanRemoval=true)
     */
    private Collection $userLanguages;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $organization;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected \DateTime $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected \DateTime $updated;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $status = self::STATUS_IDLE;

    /**
     * @ORM\ManyToOne(targetEntity=City::class, inversedBy="users")
     */
    private ?City $city;

    /**
     * @ORM\ManyToOne(targetEntity=Country::class, inversedBy="users")
     */
    private ?Country $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $location = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $twitterHandle = null;

    public function __construct()
    {
        $this->userLanguages = new ArrayCollection();
    }

    public function getRoles(): array
    {
        return ['ROLE_USER', 'ROLE_OAUTH_USER'];
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): bool
    {
        return true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getGithubId(): int
    {
        return $this->githubId;
    }

    public function setGithubId(int $githubId): self
    {
        $this->githubId = $githubId;

        return $this;
    }

    public function setData(array $data = []): void
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->getUsername();
    }

    /**
     * @return Collection|UserLanguage[]
     */
    public function getUserLanguages(): Collection
    {
        return $this->userLanguages;
    }

    public function addUserLanguage(UserLanguage $userLanguage): self
    {
        if (!$this->userLanguages->contains($userLanguage)) {
            $this->userLanguages[] = $userLanguage;
            $userLanguage->setUser($this);
        }

        return $this;
    }

    public function removeUserLanguage(UserLanguage $userLanguage): self
    {
        if ($this->userLanguages->removeElement($userLanguage)) {
            // set the owning side to null (unless already changed)
            if ($userLanguage->getUser() === $this) {
                $userLanguage->setUser(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOrganization(): ?bool
    {
        return $this->organization;
    }

    public function setOrganization(bool $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function setCreated(\DateTime $created): void
    {
        $this->created = $created;
    }

    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    public function setUpdated(\DateTime $updated): void
    {
        $this->updated = $updated;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getTwitterHandle(): ?string
    {
        return $this->twitterHandle;
    }

    public function setTwitterHandle(?string $twitterHandle): self
    {
        $this->twitterHandle = $twitterHandle;

        return $this;
    }
}
