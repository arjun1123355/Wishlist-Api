<?php
namespace Codilar\WishList\Api\Data;

interface WishlistItemInterface
{
    /**
     * @return int
     */
    public function getItemId(): int;

    /**
     * @param int $itemId
     * @return $this
     */
    public function setItemId(int $itemId): self;

    /**
     * @return int
     */
    public function getProductId(): int;

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId(int $productId): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku(string $sku): self;

    /**
     * @return float
     */
    public function getPrice(): float;

    /**
     * @param float $price
     * @return $this
     */
    public function setPrice(float $price): self;

    /**
     * @return float
     */
    public function getFinalPrice(): float;

    /**
     * @param float $finalPrice
     * @return $this
     */
    public function setFinalPrice(float $finalPrice): self;

    /**
     * @return string
     */
    public function getAddedAt(): string;

    /**
     * @param string $addedAt
     * @return $this
     */
    public function setAddedAt(string $addedAt): self;
}
