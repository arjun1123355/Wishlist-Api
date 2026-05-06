<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Api\WishlistApiInterface;

class AddToWishlist
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
            $product = $this->service->getProductBySku($productSku);
            if (!$product) {
                return $this->service->errorResponse('Product not found: ' . $productSku);
            }
            $wishlist    = $this->service->loadWishlist($customerId);
            $collection  = $this->service->itemCollectionFactory->create();
            $collection->addWishlistFilter($wishlist)
                ->addStoreFilter($this->service->getStoreIds())
                ->addFieldToFilter('product_id', (int)$product->getId());

            if ($collection->getSize() > 0) {
                return $this->service->newResponse()
                    ->setSuccess(false)->setMessage('Product already exists in wishlist')
                    ->setProductId((int)$product->getId())->setSku((string)$product->getSku())
                    ->setName((string)$product->getName())->setQuoteId(0);
            }
            $wishlist->addNewItem($product)->save();
            return $this->service->newResponse()
                ->setSuccess(true)->setMessage('Product added to wishlist successfully')
                ->setProductId((int)$product->getId())->setSku((string)$product->getSku())
                ->setName((string)$product->getName())->setQuoteId(0);
        } catch (\Exception $e) {
            $this->service->logger->error('AddToWishlist: ' . $e->getMessage());
            return $this->service->errorResponse($e->getMessage());
        }
    }
}
