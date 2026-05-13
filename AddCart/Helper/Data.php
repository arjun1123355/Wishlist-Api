<?php
namespace Codilar\AddCart\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLE = 'sales/cart_restriction/enable';
    const XML_PATH_MINIMUM_AMOUNT = 'sales/cart_restriction/minimum_amount';
    const XML_PATH_ERROR_MESSAGE = 'sales/cart_restriction/error_message';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getMinimumAmount()
    {
        return (float)$this->scopeConfig->getValue(
            self::XML_PATH_MINIMUM_AMOUNT,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getErrorMessage()
    {
        $message = $this->scopeConfig->getValue(
            self::XML_PATH_ERROR_MESSAGE,
            ScopeInterface::SCOPE_STORE
        );
        
        $amount = $this->getMinimumAmount();
        return str_replace('{{amount}}', $amount, $message);
    }
}
