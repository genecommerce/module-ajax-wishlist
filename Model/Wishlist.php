<?php

declare(strict_types=1);

namespace Gene\Wishlist\Model;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory;

class Wishlist extends DataObject implements SectionSourceInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Wishlist constructor.
     * @param Session $customerSession
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Session $customerSession,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($data);
    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        $data = [];
        $id = $this->customerSession->getCustomerId();
        $collection = $this->collectionFactory->create();
        $collection->filterByCustomerId($id)->load();

        /** @var \Magento\Wishlist\Model\Wishlist $wishlist */
        foreach ($collection as $wishlist) {
            /** @var \Magento\Wishlist\Model\Item $item */
            foreach ($wishlist->getItemCollection() as $item) {
                $data[] = [
                    'productId' => $item->getProductId(),
                    'wishlistItemId' => $item->getId(),
                    'wishlistId' => $wishlist->getId()
                ];
            }
        }
        return $data;
    }
}
