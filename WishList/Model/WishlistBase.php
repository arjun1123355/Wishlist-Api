<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Api\WishlistManagementInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Model\WishlistFactory;

class WishlistBase implements WishlistManagementInterface
{
    public function __construct(
        protected UserContextInterface $userContext,
        protected WishlistFactory $wishlistFactory,
        protected ProductRepositoryInterface $productRepository,
        protected Cart $cart,
        private AddProduct $addProduct,
        private RemoveProduct $removeProduct,
        private GetWishlist $getWishlist,
        private MoveToCart $moveToCart,
        private MoveToWishlist $moveToWishlist
    ) {}

    protected function getCustomerId(): int
    {
        $customerId = $this->userContext->getUserId();
        if (!$customerId || $this->userContext->getUserType() !== UserContextInterface::USER_TYPE_CUSTOMER) {
            throw new LocalizedException(__('Customer is not logged in.'));
        }
        return (int) $customerId;
    }

    protected function getCustomerWishlist()
    {
        return $this->wishlistFactory->create()->loadByCustomerId($this->getCustomerId(), true);
    }

    protected function buildItemList($wishlist): array
    {
        $result = [];
        foreach ($wishlist->getItemCollection() as $item) {
            $product  = $item->getProduct();
            $result[] = [
                'item_id'    => (int) $item->getId(),
                'product_id' => (int) $product->getId(),
                'sku'        => $product->getSku(),
                'name'       => $product->getName(),
                'price'      => (float) $product->getFinalPrice(),
                'qty'        => (float) $item->getQty(),
                'added_at'   => $item->getAddedAt()
            ];
        }
        return $result;
    }

    public function addProduct(int $productId, int $qty): array
    {
        return $this->addProduct->execute($productId, $qty);
    }

    public function removeProduct(int $itemId): array
    {
        return $this->removeProduct->execute($itemId);
    }

    public function getWishlist(): array
    {
        return $this->getWishlist->execute();
    }

    public function moveToCart(int $itemId): array
    {
        return $this->moveToCart->execute($itemId);
    }

    public function moveToWishlist(int $itemId): array
    {
        return $this->moveToWishlist->execute($itemId);
    }

    public function fetchWishlist(): array
    {
        return $this->getWishlist->execute();
    }
}
