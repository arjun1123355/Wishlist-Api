<?php
namespace Codilar\WishList\Api;

interface WishlistManagementInterface
{
    /**
     * Add a product to the logged-in customer's wishlist.
     *
     * @param int $productId
     * @param int $qty
     * @return mixed[]
     */
    public function addProduct(int $productId, int $qty): array;

    /**
     * Remove a product from the wishlist by wishlist item ID.
     *
     * @param int $itemId
     * @return mixed[]
     */
    public function removeProduct(int $itemId): array;

    /**
     * Get all wishlist items for the logged-in customer.
     *
     * @return mixed[]
     */
    public function getWishlist(): array;

    /**
     * Move a wishlist item to the cart.
     *
     * @param int $itemId
     * @return mixed[]
     */
    public function moveToCart(int $itemId): array;

    /**
     * Move a cart item to the wishlist.
     *
     * @param int $itemId  Quote item ID
     * @return mixed[]
     */
    public function moveToWishlist(int $itemId): array;

    /**
     * Fetch all wishlist items (detailed) for the logged-in customer.
     *
     * @return mixed[]
     */
    public function fetchWishlist(): array;
}
