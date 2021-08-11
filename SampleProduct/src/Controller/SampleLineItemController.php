<?php declare(strict_types=1);

namespace SampleProduct\Controller;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\Exception\InvalidQuantityException;
use Shopware\Core\Checkout\Cart\Exception\LineItemNotFoundException;
use Shopware\Core\Checkout\Cart\Exception\LineItemNotStackableException;
use Shopware\Core\Checkout\Cart\Exception\MixedLineItemTypeException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\Cart\ProductLineItemFactory;
use Shopware\Core\Content\Product\Exception\ProductNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Storefront\Controller\StorefrontController;

/**
 * @RouteScope(scopes={"storefront"})
 */
class SampleLineItemController extends StorefrontController
{
    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var SalesChannelRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductLineItemFactory
     */
    private $productLineItemFactory;

    public function __construct(
        CartService $cartService,
        SalesChannelRepositoryInterface $productRepository,
        ProductLineItemFactory $productLineItemFactory
    ) {
        $this->cartService = $cartService;
        $this->productRepository = $productRepository;
        $this->productLineItemFactory = $productLineItemFactory;
    }

    /**
     * @Since("6.0.0.0")
     * @Route("/checkout/sample-line-item/add", name="frontend.checkout.sample-line-item.add", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     *
     * requires the provided items in the following form
     * 'lineItems' => [
     *     'anyKey' => [
     *         'id' => 'someKey'
     *         'quantity' => 2,
     *         'type' => 'someType'
     *     ],
     *     'randomKey' => [
     *         'id' => 'otherKey'
     *         'quantity' => 2,
     *         'type' => 'otherType'
     *     ]
     * ]
     *
     * @throws InvalidQuantityException
     * @throws LineItemNotStackableException
     * @throws MissingRequestParameterException
     * @throws MixedLineItemTypeException
     */
    public function addSampleLineItems(Cart $cart, RequestDataBag $requestDataBag, Request $request, SalesChannelContext $salesChannelContext): Response
    {
        echo '<script>'.'console.log("samplecontroller")</script>';
        /** @var RequestDataBag|null $lineItems */
        $lineItems = $requestDataBag->get('lineItems');
        if (!$lineItems) {
            throw new MissingRequestParameterException('lineItems');
        }

        $count = 0;

        try {
            $items = [];
            /** @var RequestDataBag $lineItemData */
            foreach ($lineItems as $lineItemData) {
                $lineItem = new LineItem(
                    $lineItemData->getAlnum('id'),
                    $lineItemData->getAlnum('type'),
                    $lineItemData->get('referencedId'),
                    $lineItemData->getInt('quantity', 1),
                    $lineItemData->get('label')
                );

                $lineItem->setStackable($lineItemData->getBoolean('stackable', true));
                $lineItem->setRemovable($lineItemData->getBoolean('removable', true));

                $count += $lineItem->getQuantity();

                $newLabel = $lineItem->getLabel();
                $newLabel .= " Sample"; 

                $lineItem->setLabel($newLabel);
                echo '<script>'.'console.log('. json_encode($lineItem) .')</script>';

                $items[] = $lineItem;
            }

            $cart = $this->cartService->add($cart, $items, $salesChannelContext);

            if (!$this->traceErrors($cart)) {
                $this->addFlash(self::SUCCESS, $this->trans('checkout.addToCartSuccess', ['%count%' => $count]));
            }
        } catch (ProductNotFoundException $exception) {
            $this->addFlash(self::DANGER, $this->trans('error.addToCartError'));
        }

        return $this->createActionResponse($request);
    }

    private function traceErrors(Cart $cart): bool
    {
        if ($cart->getErrors()->count() <= 0) {
            return false;
        }

        $this->addCartErrors($cart, function (Error $error) {
            return $error->isPersistent();
        });

        return true;
    }
}
