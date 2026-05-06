<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Api\Data\WishlistResponseInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class MoveToCart
{
    public function __construct(
        private readonly WishlistService $service,
        private readonly CartManagementInterface $cartManagement,
        private readonly CartRepositoryInterface $cartRepository
    ) {}

    public function execute(string $productSku): WishlistResponseInterface
    {
        try {
            $customerId = $this->service->getAuthCustomerId();
            if (!$customerId) {
                return $this->service->errorResponse('Customer not authenticated');
            }
            $wishlist = $this->service->loadWishlist($customerId);
            if (!$wishlist->getId()) {
                return $this->service->errorResponse('Wishlist not found for customer');
            }
            $product = $this->service->getProductBySku($productSku);
            if (!$product) {
                return $this->service->errorResponse('Product not found: ' . $productSku);
            }
            $collection = $this->service->itemCollectionFactory->create();
            $collection->addWishlistFilter($wishlist)
                ->addStoreFilter($this->service->getStoreIds())
                ->addFieldToFilter('product_id', (int)$product->getId());

            $wishlistItem = $collection->getFirstItem();
            if (!$wishlistItem || !$wishlistItem->getId()) {
                return $this->service->errorResponse('Item not found in wishlist');
            }
            try {
                $quote = $this->cartRepository->getActiveForCustomer($customerId);
            } catch (\Exception) {
                $quoteId = $this->cartManagement->createEmptyCartForCustomer($customerId);
                $quote   = $this->cartRepository->get($quoteId);
            }
            $quote->addProduct($product, new DataObject(['qty' => 1]));
            $this->cartRepository->save($quote);
            $wishlistItem->delete();
            $wishlist->save();

            return $this->service->newResponse()
                ->setSuccess(true)->setMessage('Product moved to cart successfully')
                ->setProductId((int)$product->getId())->setSku((string)$product->getSku())
                ->setName((string)$product->getName())->setQuoteId((int)$quote->getId());
        } catch (\Exception $e) {
            $this->service->logger->error('MoveToCart: ' . $e->getMessage());
            return $this->service->errorResponse($e->getMessage());
        }
    }
}
