<?php
namespace Codilar\WishList\Model;

use Codilar\WishList\Api\Data\ActionResponseInterface;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;

class MoveToCart
{
    protected $wishlistFactory;
    protected $itemCollectionFactory;
    protected $productRepository;
    protected $cartManagement;
    protected $cartRepository;
    protected $storeManager;
    protected $userContext;
    protected $objectManager;

    public function __construct(
        WishlistFactory $wishlistFactory,
        CollectionFactory $itemCollectionFactory,
        ProductRepositoryInterface $productRepository,
        CartManagementInterface $cartManagement,
        CartRepositoryInterface $cartRepository,
        StoreManagerInterface $storeManager,
        UserContextInterface $userContext,
        ObjectManagerInterface $objectManager
    ) {
        $this->wishlistFactory       = $wishlistFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->productRepository     = $productRepository;
        $this->cartManagement        = $cartManagement;
        $this->cartRepository        = $cartRepository;
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

            $wishlistItem = $collection->getFirstItem();

            if (!$wishlistItem || !$wishlistItem->getId()) {
                return $response->setSuccess(false)->setMessage('Item not found in wishlist')
                    ->setProductId(0)->setSku('')->setName('')->setQuoteId(0);
            }

            try {
                $quote = $this->cartRepository->getActiveForCustomer($customerId);
            } catch (\Exception $e) {
                $quoteId = $this->cartManagement->createEmptyCartForCustomer($customerId);
                $quote   = $this->cartRepository->get($quoteId);
            }

            $quote->addProduct($product, new DataObject(['qty' => 1]));
            $this->cartRepository->save($quote);

            $wishlistItem->delete();
            $wishlist->save();

            return $response->setSuccess(true)
                ->setMessage('Product moved to cart successfully')
                ->setProductId((int)$product->getId())
                ->setSku((string)$product->getSku())
                ->setName((string)$product->getName())
                ->setQuoteId((int)$quote->getId());

        } catch (\Exception $e) {
            return $response->setSuccess(false)->setMessage($e->getMessage())
                ->setProductId(0)->setSku('')->setName('')->setQuoteId(0);
        }
    }
}
