<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Api\WishlistApiInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Psr\Log\LoggerInterface;

class WishlistService extends DataObject implements WishlistApiInterface
{
    public function __construct(
        public readonly WishlistFactory $wishlistFactory,
        public readonly CollectionFactory $itemCollectionFactory,
        public readonly ProductRepositoryInterface $productRepository,
        public readonly StoreManagerInterface $storeManager,
        public readonly UserContextInterface $userContext,
        public readonly LoggerInterface $logger,
        private readonly AddToWishlist $addToWishlist,
        private readonly RemoveFromWishlist $removeFromWishlist,
        private readonly GetWishlist $getWishlist,
        private readonly MoveToCart $moveToCart,
        private readonly MoveToWishlist $moveToWishlist,
        array $data = []
    ) {
        parent::__construct($data);
    }

    // ── WishlistApiInterface: API actions ─────────────────────────────────────

    public function addToWishlist(string $productSku): WishlistApiInterface
    {
        return $this->addToWishlist->execute($productSku);
    }

    public function removeFromWishlist(string $productSku): WishlistApiInterface
    {
        return $this->removeFromWishlist->execute($productSku);
    }

    public function getWishlist(): WishlistApiInterface
    {
        return $this->getWishlist->execute();
    }

    public function moveToCart(string $productSku): WishlistApiInterface
    {
        return $this->moveToCart->execute($productSku);
    }

    public function moveToWishlist(int $cartItemId): WishlistApiInterface
    {
        return $this->moveToWishlist->execute($cartItemId);
    }

    // ── WishlistApiInterface: response getters/setters ────────────────────────

    public function getSuccess(): bool                                  { return (bool)$this->getData('success'); }
    public function setSuccess(bool $v): WishlistApiInterface          { return $this->setData('success', $v); }

    public function getMessage(): string                                { return (string)$this->getData('message'); }
    public function setMessage(string $v): WishlistApiInterface        { return $this->setData('message', $v); }

    public function getProductId(): int                                 { return (int)$this->getData('product_id'); }
    public function setProductId(int $v): WishlistApiInterface         { return $this->setData('product_id', $v); }

    public function getSku(): string                                    { return (string)$this->getData('sku'); }
    public function setSku(string $v): WishlistApiInterface            { return $this->setData('sku', $v); }

    public function getName(): string                                   { return (string)$this->getData('name'); }
    public function setName(string $v): WishlistApiInterface           { return $this->setData('name', $v); }

    public function getQuoteId(): int                                   { return (int)$this->getData('quote_id'); }
    public function setQuoteId(int $v): WishlistApiInterface           { return $this->setData('quote_id', $v); }

    public function getCustomerId(): int                                { return (int)$this->getData('customer_id'); }
    public function setCustomerId(int $v): WishlistApiInterface        { return $this->setData('customer_id', $v); }

    public function getTotalItems(): int                                { return (int)$this->getData('total_items'); }
    public function setTotalItems(int $v): WishlistApiInterface        { return $this->setData('total_items', $v); }

    public function getItems(): array                                   { return $this->getData('items') ?? []; }
    public function setItems(array $v): WishlistApiInterface           { return $this->setData('items', $v); }

    public function getItemId(): int                                    { return (int)$this->getData('item_id'); }
    public function setItemId(int $v): WishlistApiInterface            { return $this->setData('item_id', $v); }

    public function getPrice(): float                                   { return (float)$this->getData('price'); }
    public function setPrice(float $v): WishlistApiInterface           { return $this->setData('price', $v); }

    public function getFinalPrice(): float                              { return (float)$this->getData('final_price'); }
    public function setFinalPrice(float $v): WishlistApiInterface      { return $this->setData('final_price', $v); }

    public function getAddedAt(): string                                { return (string)$this->getData('added_at'); }
    public function setAddedAt(string $v): WishlistApiInterface        { return $this->setData('added_at', $v); }

    // ── Shared helpers used by the 5 model files ──────────────────────────────

    public function getAuthCustomerId(): int
    {
        return (int)$this->userContext->getUserId();
    }

    public function getStoreIds(): array
    {
        return array_keys($this->storeManager->getStores());
    }

    public function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }

    public function loadWishlist(int $customerId): Wishlist
    {
        return $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
    }

    public function getProductBySku(string $sku): ?ProductInterface
    {
        try {
            return $this->productRepository->get($sku);
        } catch (NoSuchEntityException) {
            return null;
        }
    }

    public function getProductById(int $id, int $storeId = 0): ?ProductInterface
    {
        try {
            return $this->productRepository->getById($id, false, $storeId ?: null);
        } catch (NoSuchEntityException) {
            return null;
        }
    }

    public function newResponse(): WishlistApiInterface
    {
        return clone $this;
    }

    public function errorResponse(string $message): WishlistApiInterface
    {
        return $this->newResponse()
            ->setSuccess(false)->setMessage($message)
            ->setProductId(0)->setSku('')->setName('')->setQuoteId(0);
    }
}
