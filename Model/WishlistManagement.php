<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Api\WishlistManagementInterface;
use Codilar\WishList\Api\Data\ActionResponseInterface;
use Codilar\WishList\Api\Data\WishlistResponseInterface;
use Codilar\WishList\Api\Data\WishlistItemInterface;
use Magento\Framework\DataObject;

class WishlistManagement extends DataObject implements WishlistManagementInterface,
    ActionResponseInterface,
    WishlistResponseInterface,
    WishlistItemInterface
{
    protected $add;
    protected $remove;
    protected $get;
    protected $moveCart;
    protected $moveWishlist;

    public function __construct(
        AddToWishlist $add,
        RemoveFromWishlist $remove,
        GetWishlist $get,
        MoveToCart $moveCart,
        MoveToWishlist $moveWishlist,
        array $data = []
    ) {
        $this->add          = $add;
        $this->remove       = $remove;
        $this->get          = $get;
        $this->moveCart     = $moveCart;
        $this->moveWishlist = $moveWishlist;
        parent::__construct($data);
    }

    // -----------------------------------------------------------------------
    // WishlistManagementInterface — 5 API endpoints
    // -----------------------------------------------------------------------

    public function addToWishlist(string $productSku): ActionResponseInterface
    {
        return $this->add->execute($productSku);
    }

    public function removeFromWishlist(string $productSku): ActionResponseInterface
    {
        return $this->remove->execute($productSku);
    }

    public function getWishlist(): WishlistResponseInterface
    {
        return $this->get->execute();
    }

    public function moveToCart(string $productSku): ActionResponseInterface
    {
        return $this->moveCart->execute($productSku);
    }

    public function moveToWishlist(int $cartItemId): ActionResponseInterface
    {
        return $this->moveWishlist->execute($cartItemId);
    }

    // -----------------------------------------------------------------------
    // ActionResponseInterface — used by add/remove/moveToCart/moveToWishlist
    // -----------------------------------------------------------------------

    public function getSuccess(): bool
    {
        return (bool)$this->getData('success');
    }

    public function setSuccess(bool $success): self
    {
        return $this->setData('success', $success);
    }

    public function getMessage(): string
    {
        return (string)$this->getData('message');
    }

    public function setMessage(string $message): self
    {
        return $this->setData('message', $message);
    }

    public function getProductId(): int
    {
        return (int)$this->getData('product_id');
    }

    public function setProductId(int $productId): self
    {
        return $this->setData('product_id', $productId);
    }

    public function getSku(): string
    {
        return (string)$this->getData('sku');
    }

    public function setSku(string $sku): self
    {
        return $this->setData('sku', $sku);
    }

    public function getName(): string
    {
        return (string)$this->getData('name');
    }

    public function setName(string $name): self
    {
        return $this->setData('name', $name);
    }

    public function getQuoteId(): int
    {
        return (int)$this->getData('quote_id');
    }

    public function setQuoteId(int $quoteId): self
    {
        return $this->setData('quote_id', $quoteId);
    }

    // -----------------------------------------------------------------------
    // WishlistResponseInterface — used by getWishlist
    // -----------------------------------------------------------------------

    public function getCustomerId(): int
    {
        return (int)$this->getData('customer_id');
    }

    public function setCustomerId(int $customerId): self
    {
        return $this->setData('customer_id', $customerId);
    }

    public function getTotalItems(): int
    {
        return (int)$this->getData('total_items');
    }

    public function setTotalItems(int $totalItems): self
    {
        return $this->setData('total_items', $totalItems);
    }

    public function getItems(): array
    {
        return $this->getData('items') ?? [];
    }

    public function setItems(array $items): self
    {
        return $this->setData('items', $items);
    }

    // -----------------------------------------------------------------------
    // WishlistItemInterface — used per item inside getWishlist
    // -----------------------------------------------------------------------

    public function getItemId(): int
    {
        return (int)$this->getData('item_id');
    }

    public function setItemId(int $itemId): self
    {
        return $this->setData('item_id', $itemId);
    }

    public function getPrice(): float
    {
        return (float)$this->getData('price');
    }

    public function setPrice(float $price): self
    {
        return $this->setData('price', $price);
    }

    public function getFinalPrice(): float
    {
        return (float)$this->getData('final_price');
    }

    public function setFinalPrice(float $finalPrice): self
    {
        return $this->setData('final_price', $finalPrice);
    }

    public function getAddedAt(): string
    {
        return (string)$this->getData('added_at');
    }

    public function setAddedAt(string $addedAt): self
    {
        return $this->setData('added_at', $addedAt);
    }
}
