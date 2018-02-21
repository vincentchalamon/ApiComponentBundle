<?php

namespace Silverback\ApiComponentBundle\Entity\Component\Feature\Stacked;

use Doctrine\ORM\Mapping as ORM;
use Silverback\ApiComponentBundle\Entity\Component\Feature\FeatureItem;
use Silverback\ApiComponentBundle\Entity\Component\FileInterface;
use Silverback\ApiComponentBundle\Entity\Component\FileTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FeatureStackedItem
 * @package Silverback\ApiComponentBundle\Entity\Component\FeatureList
 * @author Daniel West <daniel@silverback.is>
 */
class FeatureStackedItem extends FeatureItem implements FileInterface
{
    use FileTrait;

    /**
     * @ORM\Column(type="text")
     * @Groups({"page"})
     * @Assert\NotBlank()
     * @var null|string
     */
    protected $description;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"page"})
     * @var null|string
     */
    protected $buttonText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"page"})
     * @var null|string
     */
    protected $buttonClass;

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return null|string
     */
    public function getButtonText(): ?string
    {
        return $this->buttonText;
    }

    /**
     * @param null|string $buttonText
     */
    public function setButtonText(?string $buttonText): void
    {
        $this->buttonText = $buttonText;
    }

    /**
     * @return null|string
     */
    public function getButtonClass(): ?string
    {
        return $this->buttonClass;
    }

    /**
     * @param null|string $buttonClass
     */
    public function setButtonClass(?string $buttonClass): void
    {
        $this->buttonClass = $buttonClass;
    }
}
