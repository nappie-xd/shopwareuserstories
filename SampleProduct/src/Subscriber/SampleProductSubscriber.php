<?php declare(strict_types=1);

namespace SampleProduct\Subscriber;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SampleProductSubscriber implements EventSubscriberInterface
{
    /** 
     * @var EntityRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        EntityRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    public static function getSubscribedEvents(): array
    {
        // Return the events to listen to as array like this:  <event to listen to> => <method to execute>
        return [
             ProductPageLoadedEvent::class => 'onProductPageLoaded',
             OffcanvasCartPageLoadedEvent::class => 'onCartLoaded',
             CheckoutCartPageLoadedEvent::class => 'onCartLoaded'
        ];
    }

    public function onProductPageLoaded(ProductPageLoadedEvent $event)
    {
        echo '<script>'.'console.log("productpageevent")</script>';
        $product = $event->getPage()->getProduct();
        $customFields = $product->getCustomFields();
        if(!(array_key_exists('custom_emz_sample_product_number', $customFields))) {
            return;
        }

        $sampleProductNumber = $customFields['custom_emz_sample_product_number'];  

        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('productNumber', 
            $sampleProductNumber));

        $context = $event->getContext();
   
        $productEntity = $this->productRepository
            ->search($criteria, $context)->first();

        if($productEntity) {
            $event->getPage()->addExtension('emz_sample_product', $productEntity);
        }
    }

    public function onCartLoaded($event) {
        $lineItems = $event->getPage()->getCart()->getLineItems()->getElements();
     
        foreach($lineItems as $lineItem) {
            echo '<script>'.'console.log("entered foreach")</script>';
            if(strpos(($lineItem->getLabel()), ' Sample')) {

                    $sCriteria = (new Criteria())
                    ->setLimit(1)
                    ->addFilter(new EqualsFilter('id',
                    $lineItem->getId()));
                                    
                    $context = $event->getContext();
                    
                    $sampleEntity = $this->productRepository
                    ->search($sCriteria, $context)
                    ->getElements();

                    $sampleProduct = array_shift($sampleEntity);
                    
                    //comment below
                    $sampleProductNumber = $sampleProduct->productNumber;
                    
                    
                    $productNumber = str_replace("P-", "", $sampleProductNumber);
                    // dd($productNumber);
                    
                    $criteria = (new Criteria())
                    ->setLimit(1)
                    ->addFilter(new EqualsFilter('productNumber',
                    $productNumber));
                    
                    $productEntity = $this->productRepository
                    ->search($criteria, $context)
                    ->getElements();

                    $product = array_shift($productEntity);
                    
                    // //getProductReferencedId from Entity
                    $productRefId = $product->id;

                    $lineItem->setReferencedId($productRefId);
                    // '503f80060b4c44f29bba21369255253f'
                    // echo '<script>'.'console.log("is true")</script>';
                    echo '<script>'.'console.log('. json_encode($criteria) .')</script>';
            }
        }
        // dd($lineItems['f7991f82280940daba9cb76afa8882a1']);
    }
}