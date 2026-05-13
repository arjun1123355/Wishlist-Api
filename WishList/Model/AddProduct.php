<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Model\WishlistBase;

class AddProduct extends WishlistBase
{
    public function execute(int $productId, int $qty): array
    {
        $wishlist = $this->getCustomerWishlist();
        $product  = $this->productRepository->getById($productId);
        $wishlist->addNewItem($product, ['qty' => $qty]);
        $wishlist->save();

        return [
            'success'    => true,
            'message'    => 'Product added to wishlist successfully.',
            'product_id' => $productId,
            'name'       => $product->getName(),
            'sku'        => $product->getSku(),
            'qty'        => $qty
        ];
    }
}
