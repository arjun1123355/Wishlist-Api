<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Model\WishlistBase;

class GetWishlist extends WishlistBase
{
    public function execute(): array
    {
        $wishlist = $this->getCustomerWishlist();
        $items    = $this->buildItemList($wishlist);

        return [
            'success'     => true,
            'wishlist_id' => (int) $wishlist->getId(),
            'customer_id' => (int) $wishlist->getCustomerId(),
            'total_items' => count($items),
            'items'       => $items
        ];
    }
}
