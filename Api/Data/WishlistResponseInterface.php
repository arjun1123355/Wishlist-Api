<?php
namespace Codilar\WishList\Api\Data;

interface WishlistResponseInterface
{
    /**
     * @return bool
     */
    public function getSuccess(): bool;

    /**
     * @param bool $success
     * @return $this
     */
    public function setSuccess(bool $success): self;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): self;

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId): self;

    /**
     * @return int
     */
    public function getTotalItems(): int;

    /**
     * @param int $totalItems
     * @return $this
     */
    public function setTotalItems(int $totalItems): self;

    /**
     * @return \Codilar\WishList\Api\Data\WishlistItemInterface[]
     */
    public function getItems(): array;

    /**
     * @param \Codilar\WishList\Api\Data\WishlistItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items): self;
}
