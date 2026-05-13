<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Model\WishlistBase;
use Magento\Framework\Exception\NoSuchEntityException;

class MoveToCart extends WishlistBase
{
    public function execute(int $itemId): array
    {
        $wishlist = $this->getCustomerWishlist();
        $item     = $wishlist->getItem($itemId);

        if (!$item || !$item->getId()) {
            throw new NoSuchEntityException(__('Wishlist item %1 not found.', $itemId));
        }

        $product = $item->getProduct();
        $item->addToCart($this->cart);
        $this->cart->save();
        $item->delete();
        $wishlist->save();

        return [
            'success'    => true,
            'message'    => 'Item moved to cart successfully.',
            'item_id'    => $itemId,
            'product_id' => (int) $product->getId(),
            'name'       => $product->getName(),
            'sku'        => $product->getSku()
        ];
    }
}
