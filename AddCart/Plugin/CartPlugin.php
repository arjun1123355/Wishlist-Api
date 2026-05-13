<?php
namespace Codilar\AddCart\Plugin;

use Codilar\AddCart\Helper\Data as Helper;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class CartPlugin
{
    private $helper;
    private $customerSession;

    public function __construct(
        Helper $helper,
        Session $customerSession
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
    }

    public function beforeAddProduct(Quote $subject, Product $product, $request = null)
    {
        if (!$this->helper->isEnabled() || !$this->shouldApplyRestriction()) {
            return [$product, $request];
        }

        if ($product->getFinalPrice() < $this->helper->getMinimumAmount()) {
            throw new LocalizedException(__($this->helper->getErrorMessage()));
        }

        return [$product, $request];
    }

    private function shouldApplyRestriction()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return true;
        }

        $customerGroupId = $this->customerSession->getCustomerGroupId();
        return $customerGroupId == 1;
    }
}
