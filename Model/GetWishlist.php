<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Api\Data\WishlistResponseInterface;
use Codilar\WishList\Api\Data\WishlistItemInterface;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\ObjectManagerInterface;

class GetWishlist
{
    protected $wishlistFactory;
    protected $itemCollectionFactory;
    protected $productRepository;
    protected $storeManager;
    protected $userContext;
    protected $objectManager;

    public function __construct(
        WishlistFactory $wishlistFactory,
        CollectionFactory $itemCollectionFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        UserContextInterface $userContext,
        ObjectManagerInterface $objectManager
    ) {
        $this->wishlistFactory       = $wishlistFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->productRepository     = $productRepository;
        $this->storeManager          = $storeManager;
        $this->userContext           = $userContext;
        $this->objectManager         = $objectManager;
    }

    public function execute(): WishlistResponseInterface
    {
        /** @var WishlistResponseInterface $response */
        $response = $this->objectManager->create(WishlistResponseInterface::class);

        $customerId = $this->userContext->getUserId();

        if (!$customerId) {
            return $response->setSuccess(false)->setMessage('Customer not authenticated')
                ->setCustomerId(0)->setTotalItems(0)->setItems([]);
        }

        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);

        if (!$wishlist->getId()) {
            return $response->setSuccess(true)->setMessage('Wishlist is empty')
                ->setCustomerId((int)$customerId)->setTotalItems(0)->setItems([]);
        }

        $storeIds = [];
        foreach ($this->storeManager->getStores() as $store) {
            $storeIds[] = (int)$store->getId();
        }

        $collection = $this->itemCollectionFactory->create();
        $collection->addWishlistFilter($wishlist);
        $collection->addStoreFilter($storeIds);

        $items = [];
        foreach ($collection as $item) {
            /** @var WishlistItemInterface $wishlistItem */
            $wishlistItem = $this->objectManager->create(WishlistItemInterface::class);
            $wishlistItem->setItemId((int)$item->getId());
            $wishlistItem->setProductId((int)$item->getProductId());
            $wishlistItem->setAddedAt((string)$item->getAddedAt());

            try {
                $product = $this->productRepository->getById(
                    $item->getProductId(),
                    false,
                    $this->storeManager->getStore()->getId()
                );
                $wishlistItem->setName((string)$product->getName());
                $wishlistItem->setSku((string)$product->getSku());
                $wishlistItem->setPrice((float)$product->getPrice());
                $wishlistItem->setFinalPrice((float)$product->getFinalPrice());
            } catch (\Exception $e) {
                $wishlistItem->setName('')->setSku('')->setPrice(0.0)->setFinalPrice(0.0);
            }

            $items[] = $wishlistItem;
        }

        return $response->setSuccess(true)->setMessage('')
            ->setCustomerId((int)$customerId)
            ->setTotalItems(count($items))
            ->setItems($items);
    }
}
