<?php
namespace Codilar\WishList\Api\Data;

interface WishlistResponseInterface
{
    /** @return bool */
    public function getSuccess(): bool;
    /** @param bool $success @return $this */
    public function setSuccess(bool $success): WishlistResponseInterface;

    /** @return string */
    public function getMessage(): string;
    /** @param string $message @return $this */
    public function setMessage(string $message): WishlistResponseInterface;

    /** @return int */
    public function getProductId(): int;
    /** @param int $productId @return $this */
    public function setProductId(int $productId): WishlistResponseInterface;

    /** @return string */
    public function getSku(): string;
    /** @param string $sku @return $this */
    public function setSku(string $sku): WishlistResponseInterface;

    /** @return string */
    public function getName(): string;
    /** @param string $name @return $this */
    public function setName(string $name): WishlistResponseInterface;

    /** @return int */
    public function getQuoteId(): int;
    /** @param int $quoteId @return $this */
    public function setQuoteId(int $quoteId): WishlistResponseInterface;

    /** @return int */
    public function getCustomerId(): int;
    /** @param int $customerId @return $this */
    public function setCustomerId(int $customerId): WishlistResponseInterface;

    /** @return int */
    public function getTotalItems(): int;
    /** @param int $totalItems @return $this */
    public function setTotalItems(int $totalItems): WishlistResponseInterface;

    /**
     * @return \Codilar\WishList\Api\Data\WishlistResponseInterface[]
     */
    public function getItems(): array;
    /**
     * @param \Codilar\WishList\Api\Data\WishlistResponseInterface[] $items
     * @return $this
     */
    public function setItems(array $items): WishlistResponseInterface;

    /** @return int */
    public function getItemId(): int;
    /** @param int $itemId @return $this */
    public function setItemId(int $itemId): WishlistResponseInterface;

    /** @return float */
    public function getPrice(): float;
    /** @param float $price @return $this */
    public function setPrice(float $price): WishlistResponseInterface;

    /** @return float */
    public function getFinalPrice(): float;
    /** @param float $finalPrice @return $this */
    public function setFinalPrice(float $finalPrice): WishlistResponseInterface;

    /** @return string */
    public function getAddedAt(): string;
    /** @param string $addedAt @return $this */
    public function setAddedAt(string $addedAt): WishlistResponseInterface;
}
