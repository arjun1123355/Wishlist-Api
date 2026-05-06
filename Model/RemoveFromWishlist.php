<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Api\WishlistApiInterface;

class RemoveFromWishlist
{
    public function __construct(
        private readonly WishlistService $service
    ) {}

    public function execute(string $productSku): WishlistApiInterface
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

            $item = $collection->getFirstItem();
            if (!$item || !$item->getId()) {
                return $this->service->errorResponse('Item not found in wishlist');
            }
            $item->delete();
            $wishlist->save();
            return $this->service->newResponse()
                ->setSuccess(true)->setMessage('Item removed from wishlist successfully')
                ->setProductId((int)$product->getId())->setSku((string)$product->getSku())
                ->setName((string)$product->getName())->setQuoteId(0);
        } catch (\Exception $e) {
            $this->service->logger->error('RemoveFromWishlist: ' . $e->getMessage());
            return $this->service->errorResponse($e->getMessage());
        }
    }
}
