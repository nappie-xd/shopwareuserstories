<?php declare(strict_types=1);

namespace ProductHover\Subscriber;

use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class HoverImageSubscriber implements EventSubscriberInterface {

    public static function getSubscribedEvents()
    {
        return [
            ProductListingCriteriaEvent::class => 'onListingCriteria'
        ];
    }

    public function onListingCriteria(ProductListingCriteriaEvent $event): void {
        $event->getCriteria()->addAssociation('media');
    }
}