<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Model\WishlistBase;
use Magento\Framework\Exception\NoSuchEntityException;

class MoveToWishlist extends WishlistBase
{
    public function execute(int $itemId): array
    {
        $quote     = $this->cart->getQuote();
        $quoteItem = $quote->getItemById($itemId);

        if (!$quoteItem) {
            throw new NoSuchEntityException(__('Cart item %1 not found.', $itemId));
        }

        $product  = $quoteItem->getProduct();
        $wishlist = $this->getCustomerWishlist();
        $wishlist->addNewItem($product, ['qty' => $quoteItem->getQty()]);
        $wishlist->save();

        $quote->removeItem($itemId);
        $quote->collectTotals()->save();

        return [
            'success'    => true,
            'message'    => 'Item moved to wishlist successfully.',
            'item_id'    => $itemId,
            'product_id' => (int) $product->getId(),
            'name'       => $product->getName(),
            'sku'        => $product->getSku()
        ];
    }
}
