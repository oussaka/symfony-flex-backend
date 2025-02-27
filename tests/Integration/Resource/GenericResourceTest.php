<?php
declare(strict_types = 1);
/**
 * /tests/Integration/Resource/GenericResourceTest.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

namespace App\Tests\Integration\Resource;

use App\DTO\RestDtoInterface;
use App\DTO\User\User as UserDto;
use App\Entity\ApiKey as ApiKeyEntity;
use App\Entity\User as UserEntity;
use App\Repository\UserRepository;
use App\Resource\UserResource;
use App\Rest\RepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Generator;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;
use UnexpectedValueException;
use function get_class;

/**
 * Class GenericResourceTest
 *
 * @package App\Tests\Integration\Resource
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class GenericResourceTest extends KernelTestCase
{
    private $dtoClass = UserDto::class;
    private $resourceClass = UserResource::class;
    private $entityClass = UserEntity::class;

    /**
     * @var UserResource
     */
    private $resource;

    /**
     * @return EntityManagerInterface|Object
     */
    private static function getEntityManager(): EntityManagerInterface
    {
        return static::$container->get('doctrine')->getManager();
    }

    public function testThatGetDtoClassThrowsAnExceptionWithoutDto(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp('/DTO class not specified for \'.*\' resource/');

        $this->resource->setDtoClass('');
        $this->resource->getDtoClass();
    }

    public function testThatGetDtoClassReturnsExpectedDto(): void
    {
        $this->resource->setDtoClass('foobar');

        static::assertSame('foobar', $this->resource->getDtoClass());
    }

    /**
     * @throws Throwable
     */
    public function testThatGetEntityNameCallsExpectedRepositoryMethod(): void
    {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('getEntityName');

        $this->resource->setRepository($repository);
        $this->resource->getEntityName();

        unset($repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatGetReferenceCallsExpectedRepositoryMethod(): void
    {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('getReference');

        $this->resource->setRepository($repository);
        $this->resource->getReference('some id');

        unset($repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatGetAssociationsCallsExpectedRepositoryMethod(): void
    {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('getAssociations');

        $this->resource->setRepository($repository);
        $this->resource->getAssociations();

        unset($repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatGetDtoForEntityCallsExpectedRepositoryMethod(): void
    {
        $entity = $this->getEntityMock();

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->with('some id')
            ->willReturn($entity);

        /** @var MockObject|RestDtoInterface $dto */
        $dto = $this->getDtoMockBuilder()->getMock();

        $this->resource->setRepository($repository);

        /** @noinspection UnnecessaryAssertionInspection */
        static::assertInstanceOf(
            RestDtoInterface::class,
            $this->resource->getDtoForEntity('some id', get_class($dto), $dto)
        );

        unset($dto, $repository, $entity);
    }

    /**
     * @throws Throwable
     */
    public function testThatGetDtoForEntityThrowsAnExceptionIfEntityWasNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Not found');

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->with('some id')
            ->willReturn(null);

        /** @var MockObject|RestDtoInterface $dto */
        $dto = $this->getDtoMockBuilder()->getMock();

        $this->resource->setRepository($repository);
        $this->resource->getDtoForEntity('some id', get_class($dto), $dto);

        unset($repository);
    }

    /**
     * @dataProvider dataProviderTestThatFindCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     *
     * @throws Throwable
     */
    public function testThatFindCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findByAdvanced')
            ->with(...$expectedArguments);

        $this->resource->setRepository($repository);
        $this->resource->find(...$arguments);

        unset($repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatFindOneCallsExpectedRepositoryMethod(): void
    {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findAdvanced')
            ->withAnyParameters();

        $this->resource->setRepository($repository);
        $this->resource->findOne('some id');

        unset($repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatFindOneThrowsAnExceptionIfEntityWasNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Not found');

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findAdvanced')
            ->withAnyParameters()
            ->willReturn(null);

        $this->resource->setRepository($repository);
        $this->resource->findOne('some id', true);

        unset($repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatFindOneWontThrowAnExceptionIfEntityWasFound(): void
    {
        $entity = $this->getEntityMock();

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findAdvanced')
            ->withAnyParameters()
            ->willReturn($entity);

        $this->resource->setRepository($repository);

        static::assertSame($entity, $this->resource->findOne('some id', true));

        unset($repository, $entity);
    }

    /**
     * @dataProvider dataProviderTestThatFindOneByCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     *
     * @throws Throwable
     */
    public function testThatFindOneByCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(...$expectedArguments);

        $this->resource->setRepository($repository);
        $this->resource->findOneBy(...$arguments);

        unset($repository);
    }

    /**
     *
     * @throws Throwable
     */
    public function testThatFindOneByThrowsAnExceptionIfEntityWasNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Not found');

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->withAnyParameters()
            ->willReturn(null);

        $this->resource->setRepository($repository);
        $this->resource->findOneBy([], null, true);

        unset($repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatFindOneByWontThrowAnExceptionIfEntityWasFound(): void
    {
        $entity = $this->getEntityMock();

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->withAnyParameters()
            ->willReturn($entity);

        $this->resource->setRepository($repository);

        static::assertSame($entity, $this->resource->findOneBy([], null, true));

        unset($repository, $entity);
    }

    /**
     * @dataProvider dataProviderTestThatCountCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     *
     * @throws Throwable
     */
    public function testThatCountCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('countAdvanced')
            ->with(...$expectedArguments);

        $this->resource->setRepository($repository);
        $this->resource->count(...$arguments);

        unset($repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatSaveMethodCallsExpectedRepositoryMethod(): void
    {
        $entity = new ApiKeyEntity();

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('save')
            ->with($entity);

        $this->resource->setRepository($repository);

        static::assertSame($entity, $this->resource->save($entity));

        unset($repository, $entity);
    }

    /**
     * @throws Throwable
     */
    public function testThatCreateMethodThrowsAnErrorWithInvalidDto(): void
    {
        $this->expectException(ValidatorException::class);

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $dto = new $this->dtoClass();

        $this->resource->setRepository($repository);
        $this->resource->create($dto);

        unset($dto, $repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatCreateMethodCallsExpectedMethods(): void
    {
        /** @var MockObject|UserRepository|RepositoryInterface $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('getEntityName')
            ->willReturn($this->entityClass);

        $repository
            ->expects(static::once())
            ->method('save');

        /** @var MockObject|ValidatorInterface $validator */
        $validator = $this->getMockBuilder(ValidatorInterface::class)->getMock();

        /** @var MockObject|UserRepository|ConstraintViolationListInterface $repository */
        $constraintViolationList = $this->getMockBuilder(ConstraintViolationListInterface::class)->getMock();

        $constraintViolationList
            ->expects(static::exactly(2))
            ->method('count')
            ->willReturn(0);

        $validator
            ->expects(static::exactly(2))
            ->method('validate')
            ->willReturn($constraintViolationList);

        /** @var MockObject|RestDtoInterface $dto */
        $dto = $this->getDtoMockBuilder()->getMock();

        $dto
            ->expects(static::once())
            ->method('update');

        $this->resource->setRepository($repository);
        $this->resource->setValidator($validator);
        $this->resource->create($dto);

        unset($dto, $validator, $repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatUpdateMethodThrowsAnExceptionIfEntityWasNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Not found');

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->with('some id')
            ->willReturn(null);

        $dto = new $this->dtoClass();

        $this->resource->setRepository($repository);
        $this->resource->update('some id', $dto);

        unset($dto, $repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatUpdateCallsExpectedRepositoryMethod(): void
    {
        $dto = new $this->dtoClass();
        $entity = new $this->entityClass();

        $methods = [
            'setUsername'   => 'username',
            'setFirstName'  => 'first name',
            'setLastName'   => 'last name',
            'setEmail'      => 'test@test.com',
        ];

        foreach ($methods as $method => $value) {
            $dto->$method($value);
            $entity->$method($value);
        }

        /** @var MockObject|UserRepository|RepositoryInterface $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::exactly(2))
            ->method('find')
            ->with('some id')
            ->willReturn($entity);

        $repository
            ->expects(static::once())
            ->method('save')
            ->with($entity);

        $this->resource->setRepository($repository);
        $this->resource->update('some id', $dto);

        unset($repository, $entity, $dto);
    }

    /**
     * @throws Throwable
     */
    public function testThatDeleteMethodCallsExpectedRepositoryMethod(): void
    {
        $entity = $this->getEntityMock();

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->with('some id')
            ->willReturn($entity);

        $repository
            ->expects(static::once())
            ->method('remove')
            ->with($entity);

        $this->resource->setRepository($repository);

        static::assertSame($entity, $this->resource->delete('some id'));

        unset($repository, $entity);
    }

    /**
     * @dataProvider dataProviderTestThatGetIdsCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     *
     * @throws Throwable
     */
    public function testThatGetIdsCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findIds')
            ->with(...$expectedArguments);

        $this->resource->setRepository($repository);
        $this->resource->getIds(...$arguments);

        unset($repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatSaveMethodThrowsAnExceptionWithInvalidEntity(): void
    {
        $this->expectException(ValidatorException::class);

        $entity = new $this->entityClass();

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::never())
            ->method('save')
            ->with($entity);

        $this->resource->setRepository($repository);
        $this->resource->save($entity);

        unset($repository, $entity);
    }

    /**
     * @return Generator
     */
    public function dataProviderTestThatCountCallsExpectedRepositoryMethodWithCorrectParameters(): Generator
    {
        yield [
            [[], []],
            [null, null],
        ];

        yield [
            [['foo'], []],
            [['foo'], null],
        ];

        yield [
            [['foo'], ['bar']],
            [['foo'], ['bar']],
        ];
    }

    /**
     * @return Generator
     */
    public function dataProviderTestThatFindCallsExpectedRepositoryMethodWithCorrectParameters(): Generator
    {
        yield [
            [[], [], 0, 0, []],
            [null, null, null, null, null],
        ];

        yield [
            [['foo'], [], 0, 0, []],
            [['foo'], null, null, null, null],
        ];

        yield [
            [['foo'], ['foo'], 0, 0, []],
            [['foo'], ['foo'], null, null, null],
        ];

        yield [
            [['foo'], ['foo'], 1, 0, []],
            [['foo'], ['foo'], 1, null, null],
        ];

        yield [
            [['foo'], ['foo'], 1, 2, []],
            [['foo'], ['foo'], 1, 2, null],
        ];

        yield [
            [['foo'], ['foo'], 1, 2, ['foo']],
            [['foo'], ['foo'], 1, 2, ['foo']],
        ];
    }

    /**
     * @return Generator
     */
    public function dataProviderTestThatFindOneByCallsExpectedRepositoryMethodWithCorrectParameters(): Generator
    {
        yield [
            [[], []],
            [[], null],
        ];

        yield [
            [['foo'], []],
            [['foo'], null],
        ];

        yield [
            [['foo'], ['bar']],
            [['foo'], ['bar']],
        ];
    }

    /**
     * @return Generator
     */
    public function dataProviderTestThatGetIdsCallsExpectedRepositoryMethodWithCorrectParameters(): Generator
    {
        yield [
            [[], []],
            [null, null],
        ];

        yield [
            [['foo'], []],
            [['foo'], null],
        ];

        yield [
            [['foo'], ['bar']],
            [['foo'], ['bar']],
        ];
    }

    protected function setUp(): void
    {
        gc_enable();

        parent::setUp();

        static::bootKernel();

        $this->resource = static::$container->get($this->resourceClass);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->resource);

        gc_collect_cycles();
    }

    /**
     * @return MockBuilder
     */
    private function getRepositoryMockBuilder(): MockBuilder
    {
        return $this
            ->getMockBuilder(UserRepository::class)
            ->setConstructorArgs([static::getEntityManager(), new ClassMetadata($this->entityClass)]);
    }

    /**
     * @return MockObject|UserEntity
     *
     * @throws Throwable
     */
    private function getEntityMock(): MockObject
    {
        return $this->createMock($this->entityClass);
    }

    /**
     * @return MockBuilder
     */
    private function getDtoMockBuilder(): MockBuilder
    {
        return $this->getMockBuilder($this->dtoClass);
    }
}
