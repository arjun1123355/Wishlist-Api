<?php
namespace Codilar\WishList\Api;

use Codilar\WishList\Api\Data\WishlistResponseInterface;

interface WishlistApiInterface
{
    /**
     * @param string $productSku
     * @return \Codilar\WishList\Api\Data\WishlistResponseInterface
     */
    public function addToWishlist(string $productSku): WishlistResponseInterface;

    /**
     * @param string $productSku
     * @return \Codilar\WishList\Api\Data\WishlistResponseInterface
     */
    public function removeFromWishlist(string $productSku): WishlistResponseInterface;

    /**
     * @return \Codilar\WishList\Api\Data\WishlistResponseInterface
     */
    public function getWishlist(): WishlistResponseInterface;

    /**
     * @param string $productSku
     * @return \Codilar\WishList\Api\Data\WishlistResponseInterface
     */
    public function moveToCart(string $productSku): WishlistResponseInterface;

    /**
     * @param int $cartItemId
     * @return \Codilar\WishList\Api\Data\WishlistResponseInterface
     */
    public function moveToWishlist(int $cartItemId): WishlistResponseInterface;
}
