<?php
namespace Codilar\WishList\Api;

interface WishlistApiInterface
{
    /**
     * @param string $productSku
     * @return \Codilar\WishList\Api\WishlistApiInterface
     */
    public function addToWishlist(string $productSku): WishlistApiInterface;

    /**
     * @param string $productSku
     * @return \Codilar\WishList\Api\WishlistApiInterface
     */
    public function removeFromWishlist(string $productSku): WishlistApiInterface;

    /**
     * @return \Codilar\WishList\Api\WishlistApiInterface
     */
    public function getWishlist(): WishlistApiInterface;

    /**
     * @param string $productSku
     * @return \Codilar\WishList\Api\WishlistApiInterface
     */
    public function moveToCart(string $productSku): WishlistApiInterface;

    /**
     * @param int $cartItemId
     * @return \Codilar\WishList\Api\WishlistApiInterface
     */
    public function moveToWishlist(int $cartItemId): WishlistApiInterface;

    /** @return bool */
    public function getSuccess(): bool;
    /** @param bool $success @return \Codilar\WishList\Api\WishlistApiInterface */
    public function setSuccess(bool $success): WishlistApiInterface;

    /** @return string */
    public function getMessage(): string;
    /** @param string $message @return \Codilar\WishList\Api\WishlistApiInterface */
    public function setMessage(string $message): WishlistApiInterface;

    /** @return int */
    public function getProductId(): int;
    /** @param int $productId @return \Codilar\WishList\Api\WishlistApiInterface */
    public function setProductId(int $productId): WishlistApiInterface;

    /** @return string */
    public function getSku(): string;
    /** @param string $sku @return \Codilar\WishList\Api\WishlistApiInterface */
    public function setSku(string $sku): WishlistApiInterface;

    /** @return string */
    public function getName(): string;
    /** @param string $name @return \Codilar\WishList\Api\WishlistApiInterface */
    public function setName(string $name): WishlistApiInterface;

    /** @return int */
    public function getQuoteId(): int;
    /** @param int $quoteId @return \Codilar\WishList\Api\WishlistApiInterface */
    public function setQuoteId(int $quoteId): WishlistApiInterface;

    /** @return int */
    public function getCustomerId(): int;
    /** @param int $customerId @return \Codilar\WishList\Api\WishlistApiInterface */
    public function setCustomerId(int $customerId): WishlistApiInterface;

    /** @return int */
    public function getTotalItems(): int;
    /** @param int $totalItems @return \Codilar\WishList\Api\WishlistApiInterface */
    public function setTotalItems(int $totalItems): WishlistApiInterface;

    /**
     * @return \Codilar\WishList\Api\WishlistApiInterface[]
     */
    public function getItems(): array;
    /**
     * @param \Codilar\WishList\Api\WishlistApiInterface[] $items
     * @return \Codilar\WishList\Api\WishlistApiInterface
     */
    public function setItems(array $items): WishlistApiInterface;

    /** @return int */
    public function getItemId(): int;
    /** @param int $itemId @return \Codilar\WishList\Api\WishlistApiInterface */
    public function setItemId(int $itemId): WishlistApiInterface;

    /** @return float */
    public function getPrice(): float;
    /** @param float $price @return \Codilar\WishList\Api\WishlistApiInterface */
    public function setPrice(float $price): WishlistApiInterface;

    /** @return float */
    public function getFinalPrice(): float;
    /** @param float $finalPrice @return \Codilar\WishList\Api\WishlistApiInterface */
    public function setFinalPrice(float $finalPrice): WishlistApiInterface;

    /** @return string */
    public function getAddedAt(): string;
    /** @param string $addedAt @return \Codilar\WishList\Api\WishlistApiInterface */
    public function setAddedAt(string $addedAt): WishlistApiInterface;
}
