<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Api\Data\WishlistResponseInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class MoveToWishlist
{
    public function __construct(
        private readonly WishlistService $service,
        private readonly CartRepositoryInterface $cartRepository
    ) {}

    public function execute(int $cartItemId): WishlistResponseInterface
    {
        try {
            $customerId = $this->service->getAuthCustomerId();
            if (!$customerId) {
                return $this->service->errorResponse('Customer not authenticated');
            }
            try {
                $quote = $this->cartRepository->getActiveForCustomer($customerId);
            } catch (\Exception) {
                return $this->service->errorResponse('No active cart found for this customer');
            }
            $cartItem = null;
            foreach ($quote->getAllItems() as $item) {
                if ((int)$item->getId() === $cartItemId) {
                    $cartItem = $item;
                    break;
                }
            }
            if (!$cartItem) {
                return $this->service->errorResponse('Cart item not found with ID: ' . $cartItemId);
            }
            $product = $this->service->getProductById((int)$cartItem->getProductId());
            if (!$product) {
                return $this->service->errorResponse('Product not found');
            }
            $wishlist   = $this->service->loadWishlist($customerId);
            $existing   = $this->service->itemCollectionFactory->create();
            $existing->addWishlistFilter($wishlist)
                ->addStoreFilter($this->service->getStoreIds())
                ->addFieldToFilter('product_id', (int)$product->getId());

            if ($existing->getSize() === 0) {
                $wishlist->addNewItem($product)->save();
            }
            $quote->removeItem($cartItemId);
            $this->cartRepository->save($quote);

            return $this->service->newResponse()
                ->setSuccess(true)->setMessage('Product moved to wishlist successfully')
                ->setProductId((int)$product->getId())->setSku((string)$product->getSku())
                ->setName((string)$product->getName())->setQuoteId((int)$quote->getId());
        } catch (\Exception $e) {
            $this->service->logger->error('MoveToWishlist: ' . $e->getMessage());
            return $this->service->errorResponse($e->getMessage());
        }
    }
}
