<?php
namespace Codilar\WishList\Api;

interface WishlistManagementInterface
{
    /**
     * Add product to wishlist
     *
     * @param string $productSku
     * @return \Codilar\WishList\Api\Data\ActionResponseInterface
     */
    public function addToWishlist(string $productSku): \Codilar\WishList\Api\Data\ActionResponseInterface;

    /**
     * Remove product from wishlist
     *
     * @param string $productSku
     * @return \Codilar\WishList\Api\Data\ActionResponseInterface
     */
    public function removeFromWishlist(string $productSku): \Codilar\WishList\Api\Data\ActionResponseInterface;

    /**
     * Get wishlist products
     *
     * @return \Codilar\WishList\Api\Data\WishlistResponseInterface
     */
    public function getWishlist(): \Codilar\WishList\Api\Data\WishlistResponseInterface;

    /**
     * Move product from wishlist to cart
     *
     * @param string $productSku
     * @return \Codilar\WishList\Api\Data\ActionResponseInterface
     */
    public function moveToCart(string $productSku): \Codilar\WishList\Api\Data\ActionResponseInterface;

    /**
     * Move product from cart to wishlist
     *
     * @param int $cartItemId
     * @return \Codilar\WishList\Api\Data\ActionResponseInterface
     */
    public function moveToWishlist(int $cartItemId): \Codilar\WishList\Api\Data\ActionResponseInterface;
}
