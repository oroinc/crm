<?php
namespace Oro\Bundle\SearchBundle\Tests\Unit\Query\Result\Subscriber;

use Oro\Bundle\SearchBundle\Query\Result\Subscriber\PaginateResultSubscriber;
use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\SearchBundle\Query\Query;

use Knp\Component\Pager\Event\ItemsEvent;

class PaginateResultSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSubscribedEvents()
    {
        $result = PaginateResultSubscriber::getSubscribedEvents();
        $this->assertEquals('paginateResults', $result['knp_pager.items'][0]);
    }

    public function testPaginateResults()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $items[] = new Item(
            $this->om,
            'OroTestBundle:test',
            1,
            'test title',
            'http://example.com',
            'test text',
            array(
                 'alias' => 'test_product',
                 'label' => 'test product',
                 'fields' => array(
                     array(
                         'name'          => 'name',
                         'target_type'   => 'text',
                     ),
                 ),
                 'flexible_manager' => 'test_manager'
            )
        );
        $query = new Query();

        $result = new Result($query, $items);

        $itemEvent = new ItemsEvent(1, 2);
        $itemEvent->target = $result;
        $paginator = new PaginateResultSubscriber();
        $paginator->paginateResults($itemEvent);
    }
}
