<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Model\WishlistBase;
use Magento\Framework\Exception\NoSuchEntityException;

class RemoveProduct extends WishlistBase
{
    public function execute(int $itemId): array
    {
        $wishlist = $this->getCustomerWishlist();
        $item     = $wishlist->getItem($itemId);

        if (!$item || !$item->getId()) {
            throw new NoSuchEntityException(__('Wishlist item %1 not found.', $itemId));
        }

        $productName = $item->getProduct()->getName();
        $item->delete();
        $wishlist->save();

        return [
            'success' => true,
            'message' => 'Item removed from wishlist successfully.',
            'item_id' => $itemId,
            'name'    => $productName
        ];
    }
}
