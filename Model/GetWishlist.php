<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Api\WishlistApiInterface;

class GetWishlist
{
    public function __construct(
        private readonly WishlistService $service
    ) {}

    public function execute(): WishlistApiInterface
    {
        $customerId = $this->service->getAuthCustomerId();
        if (!$customerId) {
            return $this->service->newResponse()
                ->setSuccess(false)->setMessage('Customer not authenticated')
                ->setCustomerId(0)->setTotalItems(0)->setItems([]);
        }
        $wishlist = $this->service->loadWishlist($customerId);
        if (!$wishlist->getId()) {
            return $this->service->newResponse()
                ->setSuccess(true)->setMessage('Wishlist is empty')
                ->setCustomerId($customerId)->setTotalItems(0)->setItems([]);
        }
        $storeId    = $this->service->getStoreId();
        $collection = $this->service->itemCollectionFactory->create();
        $collection->addWishlistFilter($wishlist)
            ->addStoreFilter($this->service->getStoreIds());

        $items = [];
        foreach ($collection as $item) {
            $entry = $this->service->newResponse()
                ->setItemId((int)$item->getId())
                ->setProductId((int)$item->getProductId())
                ->setAddedAt((string)$item->getAddedAt());

            $product = $this->service->getProductById((int)$item->getProductId(), $storeId);
            if ($product) {
                $entry->setName((string)$product->getName())
                    ->setSku((string)$product->getSku())
                    ->setPrice((float)$product->getPrice())
                    ->setFinalPrice((float)$product->getFinalPrice());
            } else {
                $this->service->logger->warning('GetWishlist: product ' . $item->getProductId() . ' not found');
                $entry->setName('')->setSku('')->setPrice(0.0)->setFinalPrice(0.0);
            }
            $items[] = $entry;
        }
        return $this->service->newResponse()
            ->setSuccess(true)->setMessage('Wishlist fetched successfully')
            ->setCustomerId($customerId)
            ->setTotalItems(count($items))
            ->setItems($items);
    }
}
