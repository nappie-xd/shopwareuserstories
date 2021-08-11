<?php declare(strict_types=1);

namespace ProductCount\Subscriber;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Storefront\Pagelet\Header\HeaderPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductCountSubscriber implements EventSubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public static function getSubscribedEvents(): array
    {
        // echo '<script>'.'console.log("get subscribed events")</script>';
        // Return the events to listen to as array like this:  <event to listen to> => <method to execute>
        return [
            HeaderPageletLoadedEvent::class => 'onHeaderLoaded'
        ];
    }

    public function onHeaderLoaded(HeaderPageletLoadedEvent $event): void 
    {
        // echo '<script>'.'console.log("navigation loaded")</script>';
        $categoryTree = $event->getPagelet()->getNavigation()->getTree();
        $counts = $this->fetchMapping();
        //  dd($event->getPagelet());

        $event->getSalesChannelContext()->addExtension('emz_product_count', new ArrayEntity($counts));
    }

    private function fetchMapping(): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'category_id',
            'COUNT(product_id)',
        ]);
        $query->from('product_category_tree');
        $query->groupBy('category_id');

        $counts = $query->execute()->fetchAll();

        $convertedCounts = [];

        foreach ($counts as $countitem) {
            $convertedCounts[Uuid::fromBytesToHex($countitem['category_id'])] = $countitem['COUNT(product_id)'];
        }
        
        return $convertedCounts;
    }
}