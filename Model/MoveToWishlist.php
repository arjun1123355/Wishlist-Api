<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Api\Data\ActionResponseInterface;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory as WishlistItemCollectionFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\ObjectManagerInterface;

class MoveToWishlist
{
    protected $wishlistFactory;
    protected $wishlistItemCollectionFactory;
    protected $cartRepository;
    protected $productRepository;
    protected $storeManager;
    protected $userContext;
    protected $objectManager;

    public function __construct(
        WishlistFactory $wishlistFactory,
        WishlistItemCollectionFactory $wishlistItemCollectionFactory,
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        UserContextInterface $userContext,
        ObjectManagerInterface $objectManager
    ) {
        $this->wishlistFactory               = $wishlistFactory;
        $this->wishlistItemCollectionFactory = $wishlistItemCollectionFactory;
        $this->cartRepository                = $cartRepository;
        $this->productRepository             = $productRepository;
        $this->storeManager                  = $storeManager;
        $this->userContext                   = $userContext;
        $this->objectManager                 = $objectManager;
    }

    public function execute($cartItemId): ActionResponseInterface
    {
        /** @var ActionResponseInterface $response */
        $response = $this->objectManager->create(ActionResponseInterface::class);

        try {
            $customerId = $this->userContext->getUserId();

            if (!$customerId) {
                return $response->setSuccess(false)->setMessage('Customer not authenticated')
                    ->setProductId(0)->setSku('')->setName('')->setQuoteId(0);
            }

            try {
                $quote = $this->cartRepository->getActiveForCustomer($customerId);
            } catch (\Exception $e) {
                return $response->setSuccess(false)->setMessage('No active cart found for this customer')
                    ->setProductId(0)->setSku('')->setName('')->setQuoteId(0);
            }

            $cartItem = null;
            foreach ($quote->getAllItems() as $item) {
                if ((int)$item->getId() === (int)$cartItemId) {
                    $cartItem = $item;
                    break;
                }
            }

            if (!$cartItem) {
                return $response->setSuccess(false)
                    ->setMessage('Cart item not found with ID: ' . $cartItemId)
                    ->setProductId(0)->setSku('')->setName('')->setQuoteId(0);
            }

            $product  = $this->productRepository->getById($cartItem->getProductId());
            $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);

            $storeIds = [];
            foreach ($this->storeManager->getStores() as $store) {
                $storeIds[] = (int)$store->getId();
            }

            $existing = $this->wishlistItemCollectionFactory->create();
            $existing->addWishlistFilter($wishlist);
            $existing->addStoreFilter($storeIds);
            $existing->addFieldToFilter('product_id', (int)$product->getId());

            if ($existing->getSize() > 0) {
                $quote->removeItem($cartItemId);
                $this->cartRepository->save($quote);
                return $response->setSuccess(true)
                    ->setMessage('Product already in wishlist; removed from cart')
                    ->setProductId((int)$product->getId())
                    ->setSku((string)$product->getSku())
                    ->setName((string)$product->getName())
                    ->setQuoteId((int)$quote->getId());
            }

            $wishlist->addNewItem($product);
            $wishlist->save();

            $quote->removeItem($cartItemId);
            $this->cartRepository->save($quote);

            return $response->setSuccess(true)
                ->setMessage('Product moved to wishlist successfully')
                ->setProductId((int)$product->getId())
                ->setSku((string)$product->getSku())
                ->setName((string)$product->getName())
                ->setQuoteId((int)$quote->getId());

        } catch (\Exception $e) {
            return $response->setSuccess(false)->setMessage($e->getMessage())
                ->setProductId(0)->setSku('')->setName('')->setQuoteId(0);
        }
    }
}
