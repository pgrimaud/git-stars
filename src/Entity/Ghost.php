<?php

namespace App\Entity;

use App\Repository\GhostRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GhostRepository::class)
 */
class Ghost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $github_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGithubId(): ?int
    {
        return $this->github_id;
    }

    public function setGithubId(int $github_id): self
    {
        $this->github_id = $github_id;

        return $this;
    }
}
