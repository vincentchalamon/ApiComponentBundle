<?php

/*
 * This file is part of the Silverback API Component Bundle Project
 *
 * (c) Daniel West <daniel@silverback.is>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serializer;

use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Silverback\ApiComponentBundle\Serializer\ApiNormalizer;
use Silverback\ApiComponentBundle\Tests\Functional\TestBundle\Entity\FileComponent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Traversable;

class ApiNormalizerTest extends TestCase
{
    private ApiNormalizer $apiNormalizer;
    /**
     * @var ResourceClassResolverInterface|MockObject
     */
    private $resourceClassResolverMock;
    /**
     * @var EntityManagerInterface|MockObject
     */
    private $entityManagerMock;
    /**
     * @var MockObject|NormalizerInterface
     */
    private $normalizerMock;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->resourceClassResolverMock = $this->createMock(ResourceClassResolverInterface::class);
        $this->normalizerMock = $this->createMock(NormalizerInterface::class);
        $this->apiNormalizer = new ApiNormalizer($this->entityManagerMock, $this->resourceClassResolverMock);
        $this->apiNormalizer->setNormalizer($this->normalizerMock);
    }

//    public function test_normalizer_is_called(): void
//    {
//
//    }

    public function tests_does_not_support_normalization_never_reaching_resource_class_resolver(): void
    {
        $this->resourceClassResolverMock
            ->expects($this->never())
            ->method('isResourceClass');

        $format = 'jsonld';
        $this->assertFalse($this->apiNormalizer->supportsNormalization(new FileComponent(), $format, ['API_NORMALIZER_ALREADY_CALLED' => true]));
        $this->assertFalse($this->apiNormalizer->supportsNormalization([], $format, []));
        $this->assertFalse($this->apiNormalizer->supportsNormalization('string', $format, []));
        $traversable = $this->createMock(Traversable::class);
        $this->assertFalse($this->apiNormalizer->supportsNormalization($traversable, $format, []));
    }

    public function test_does_not_support_non_api_platform_resource_normalization(): void
    {
        $dummyComponent = new FileComponent();
        $format = 'jsonld';

        $this->resourceClassResolverMock
            ->expects($this->once())
            ->method('isResourceClass')
            ->with(FileComponent::class)
            ->willReturn(false);
        $this->assertFalse($this->apiNormalizer->supportsNormalization($dummyComponent, $format, []));
    }

    public function tests_supports_normalization(): void
    {
        $dummyComponent = new FileComponent();
        $format = 'jsonld';

        $this->resourceClassResolverMock
            ->expects($this->once())
            ->method('isResourceClass')
            ->with(FileComponent::class)
            ->willReturn(true);
        $this->assertTrue($this->apiNormalizer->supportsNormalization($dummyComponent, $format, []));
    }

    public function test_has_cacheable_supports_method(): void
    {
        // context changes so will support the first time and not in future to prevent infinite loop
        $this->assertFalse($this->apiNormalizer->hasCacheableSupportsMethod());
    }

    public function test_normalization_result_not_an_array(): void
    {
        $dummyComponent = new FileComponent();
        $format = 'jsonld';

        $this->normalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->with($dummyComponent, $format, ['API_NORMALIZER_ALREADY_CALLED' => true])
            ->willReturn('not an array');

        $result = $this->apiNormalizer->normalize($dummyComponent, $format, []);
        $this->assertEquals('not an array', $result);
    }

    public function test_normalization_result_entity_is_persisted(): void
    {
        $dummyComponent = new FileComponent();
        $format = 'jsonld';

        $this->normalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->with($dummyComponent, $format, ['API_NORMALIZER_ALREADY_CALLED' => true, 'default_context_param' => 'default_value'])
            ->willReturn(['property' => 'value']);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('contains')
            ->with($dummyComponent)
            ->willReturn(true);

        $result = $this->apiNormalizer->normalize($dummyComponent, $format, ['default_context_param' => 'default_value']);
        $this->assertEquals(['property' => 'value', $this->apiNormalizer::IS_PERSISTED_DATA_KEY => true], $result);
    }

    public function test_normalization_result_entity_is_not_persisted(): void
    {
        $dummyComponent = new FileComponent();
        $format = 'jsonld';

        $this->normalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->with($dummyComponent, $format, ['API_NORMALIZER_ALREADY_CALLED' => true])
            ->willReturn(['property' => 'value']);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('contains')
            ->with($dummyComponent)
            ->willReturn(false);

        $result = $this->apiNormalizer->normalize($dummyComponent, $format, []);
        $this->assertEquals(['property' => 'value', $this->apiNormalizer::IS_PERSISTED_DATA_KEY => false], $result);
    }
}
