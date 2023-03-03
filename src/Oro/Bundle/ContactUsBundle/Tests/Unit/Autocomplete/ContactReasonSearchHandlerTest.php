<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Unit\Autocomplete;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ContactUsBundle\Autocomplete\ContactReasonSearchHandler;
use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bundle\ContactUsBundle\Tests\Unit\Stub\ContactReasonStub;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Component\Testing\ReflectionUtil;

class ContactReasonSearchHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var ContactReasonSearchHandler */
    private $searchHandler;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $this->searchHandler = new ContactReasonSearchHandler(
            $this->doctrineHelper,
            PropertyAccess::createPropertyAccessor()
        );
    }

    private function getContactReason(int $id, string $defaultTitle): ContactReasonStub
    {
        $contactReason = new ContactReasonStub($defaultTitle);
        ReflectionUtil::setId($contactReason, $id);

        return $contactReason;
    }

    public function testSearchWithoutQuery()
    {
        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([
                $this->getContactReason(2, 'Title #2'),
                $this->getContactReason(3, 'Title #3')
            ]);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->any())
            ->method('expr')
            ->willReturn(new Expr());
        $queryBuilder->expects($this->once())
            ->method('innerJoin')
            ->with('contact_reason.titles', 'titles', Join::WITH, 'titles.localization IS NULL');
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('contact_reason.deletedAt IS NULL');
        $queryBuilder->expects($this->never())
            ->method('andWhere');
        $queryBuilder->expects($this->never())
            ->method('setParameter');
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with(ContactReason::class)
            ->willReturn($repository);

        $this->assertEquals(['results' => [
            ['id' => 2, 'defaultTitle' => 'Title #2'],
            ['id' => 3, 'defaultTitle' => 'Title #3']
        ], 'more' => false], $this->searchHandler->search('', 0, 5));
    }

    public function testSearchWithQuery()
    {
        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([
                $this->getContactReason(2, 'Title #2'),
                $this->getContactReason(3, 'Title #3')
            ]);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->any())
            ->method('expr')
            ->willReturn(new Expr());
        $queryBuilder->expects($this->once())
            ->method('innerJoin')
            ->with('contact_reason.titles', 'titles', Join::WITH, 'titles.localization IS NULL');
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('contact_reason.deletedAt IS NULL');
        $queryBuilder->expects($this->once())
            ->method('andWhere')
            ->with(new Comparison('titles.string', 'LIKE', ':title'));
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('title', '%Search string...%');
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with(ContactReason::class)
            ->willReturn($repository);

        $this->assertEquals(['results' => [
            ['id' => 2, 'defaultTitle' => 'Title #2'],
            ['id' => 3, 'defaultTitle' => 'Title #3']
        ], 'more' => false], $this->searchHandler->search('Search string...', 0, 5));
    }

    public function testGetProperties()
    {
        $this->assertEquals(['defaultTitle'], $this->searchHandler->getProperties());
    }

    public function testGetEntityName()
    {
        $this->assertEquals(ContactReason::class, $this->searchHandler->getEntityName());
    }

    public function testConvertItem()
    {
        $entity = $this->getContactReason(2, 'Title');

        $this->assertEquals(['id' => 2, 'defaultTitle' => 'Title'], $this->searchHandler->convertItem($entity));
    }
}
