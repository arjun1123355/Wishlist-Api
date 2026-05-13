<?php
namespace Codilar\NewsLetter\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class CustomerRegisterSuccess implements ObserverInterface
{
    protected $subscriberFactory;
    protected $customerRepository;
    protected $groupRepository;
    protected $searchCriteriaBuilder;

    public function __construct(
        SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->subscriberFactory = $subscriberFactory;
        $this->customerRepository = $customerRepository;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        // Subscribe to newsletter with STATUS_SUBSCRIBED directly
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByEmail($customer->getEmail());
        $subscriber->setStatus(\Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED);
        $subscriber->setSubscriberEmail($customer->getEmail());
        $subscriber->setSubscriberConfirmCode($subscriber->RandomSequence());
        $subscriber->setCustomerId($customer->getId());
        $subscriber->setStoreId($customer->getStoreId());
        $subscriber->save();

        // Assign to "Website" customer group
        $groupId = $this->getWebsiteGroupId();
        if ($groupId) {
            $customer->setGroupId($groupId);
            $this->customerRepository->save($customer);
        }
    }

    protected function getWebsiteGroupId()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('customer_group_code', 'Website', 'eq')
            ->create();
        
        $groups = $this->groupRepository->getList($searchCriteria)->getItems();
        
        if (!empty($groups)) {
            return reset($groups)->getId();
        }
        
        return null;
    }
}
