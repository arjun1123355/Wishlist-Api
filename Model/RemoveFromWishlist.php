<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Api\Data\ActionResponseInterface;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\ObjectManagerInterface;

class RemoveFromWishlist
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

    public function execute($productSku): ActionResponseInterface
    {
        /** @var ActionResponseInterface $response */
        $response = $this->objectManager->create(ActionResponseInterface::class);

        try {
            $customerId = $this->userContext->getUserId();

            if (!$customerId) {
                return $response->setSuccess(false)->setMessage('Customer not authenticated')
                    ->setProductId(0)->setSku('')->setName('')->setQuoteId(0);
            }

            $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);

            if (!$wishlist->getId()) {
                return $response->setSuccess(false)->setMessage('Wishlist not found for customer')
                    ->setProductId(0)->setSku('')->setName('')->setQuoteId(0);
            }

            $product = $this->productRepository->get($productSku);

            $storeIds = [];
            foreach ($this->storeManager->getStores() as $store) {
                $storeIds[] = (int)$store->getId();
            }

            $collection = $this->itemCollectionFactory->create();
            $collection->addWishlistFilter($wishlist);
            $collection->addStoreFilter($storeIds);
            $collection->addFieldToFilter('product_id', (int)$product->getId());

            $item = $collection->getFirstItem();

            if (!$item || !$item->getId()) {
                return $response->setSuccess(false)->setMessage('Item not found in wishlist')
                    ->setProductId(0)->setSku('')->setName('')->setQuoteId(0);
            }

            $item->delete();
            $wishlist->save();

            return $response->setSuccess(true)
                ->setMessage('Item removed from wishlist successfully')
                ->setProductId((int)$product->getId())
                ->setSku((string)$productSku)
                ->setName((string)$product->getName())
                ->setQuoteId(0);

        } catch (\Exception $e) {
            return $response->setSuccess(false)->setMessage($e->getMessage())
                ->setProductId(0)->setSku('')->setName('')->setQuoteId(0);
        }
    }
}
