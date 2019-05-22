<?php

declare(strict_types=1);

namespace Silverback\ApiComponentBundle\Serializer;

use ApiPlatform\Core\Exception\RuntimeException;
use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Silverback\ApiComponentBundle\Entity\Component\AbstractComponent;
use Silverback\ApiComponentBundle\Entity\Component\ComponentLocation;
use Silverback\ApiComponentBundle\Entity\Component\Navigation\AbstractNavigation;
use Silverback\ApiComponentBundle\Entity\Content\AbstractContent;
use Silverback\ApiComponentBundle\Entity\Content\Page\Dynamic\DynamicContent;
use Silverback\ApiComponentBundle\Entity\Content\Page\DynamicPage;
use Silverback\ApiComponentBundle\Entity\Layout\Layout;
use Silverback\ApiComponentBundle\Entity\Route\Route;
use Silverback\ApiComponentBundle\Entity\SortableInterface;
use Symfony\Component\HttpFoundation\Request;

class ApiContextBuilder implements SerializerContextBuilderInterface
{
    /**
     * @var string[][]
     */
    public const CLASS_GROUP_MAPPING = [
        AbstractComponent::class => ['component'],
        AbstractNavigation::class => ['component'],
        ComponentLocation::class => ['component'],
        AbstractContent::class => ['content'],
        DynamicContent::class => ['content'],
        Route::class => ['route'],
        Layout::class => ['layout'],
        DynamicPage::class => ['dynamic_content', 'content'],
        SortableInterface::class => ['sortable']
    ];

    /**
     * @var SerializerContextBuilderInterface
     */
    private $decorated;

    public function __construct(SerializerContextBuilderInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @param string $group
     * @param bool $normalization
     * @return array
     */
    private function getGroupNames(string $group, bool $normalization): array
    {
        return [$group, $group . ($normalization ? '_read' : '_write')];
    }

    /**
     * @param $className
     * @param $matchClassName
     * @return bool
     */
    private function matchClass($className, $matchClassName): bool
    {
        return $className === $matchClassName || is_subclass_of($className, $matchClassName);
    }

    /**
     * @param string $subject
     * @param bool $normalization
     * @return array
     */
    public function getGroups(string $subject, bool $normalization): array
    {
        /** @var string[] $groups */
        $groups = [['default', 'default' . ($normalization ? '_read' : '_write')]];
        foreach (self::CLASS_GROUP_MAPPING as $class => $groupMapping) {
            if ($this->matchClass($subject, $class)) {
                foreach ($groupMapping as $group) {
                    $groups[] = $this->getGroupNames($group, $normalization);
                }
            }
        }
        return $groups;
    }

    /**
     * @param Request $request
     * @param bool $normalization
     * @param array|null $extractedAttributes
     * @return array
     * @throws RuntimeException
     */
    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $ctxGroups = array_key_exists('groups', $context) ? (array) $context['groups'] : [];
        if (\in_array('none', $ctxGroups, true)) {
            return $context;
        }
        $subject = $request->attributes->get('_api_resource_class');
        $groups = $this->getGroups($subject, $normalization);
        if (\count($groups)) {
            $ctxGroups = array_merge($ctxGroups, ...$groups);
        }
        $context['groups'] = $ctxGroups;
        return $context;
    }
}
