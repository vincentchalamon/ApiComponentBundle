<?php

namespace Silverback\ApiComponentBundle\Factory\Entity\Component\Feature;

use Silverback\ApiComponentBundle\Entity\Component\Feature\AbstractFeatureItem;
use Silverback\ApiComponentBundle\Factory\Entity\Component\AbstractComponentFactory;

abstract class AbstractFeatureItemFactory extends AbstractComponentFactory
{
    /**
     * @param AbstractFeatureItem $component
     * @inheritdoc
     */
    protected function init($component, ?array $ops = null): void
    {
        parent::init($component, $ops);
        $component->setLabel($this->ops['label']);
        $component->setLink($this->ops['link']);
    }

    /**
     * @inheritdoc
     */
    public static function defaultOps(): array
    {
        return array_merge(
            parent::defaultOps(),
            [
                'label' => '',
                'link' => null
            ]
        );
    }
}
