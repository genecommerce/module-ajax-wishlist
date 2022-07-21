<?php

declare(strict_types=1);

namespace Gene\Wishlist\CustomerData;

class Wishlist extends \Magento\Wishlist\CustomerData\Wishlist
{
    /**
     * Array size for section data - default is 3
     * @var int
     */
    const SIDEBAR_ITEMS_NUMBER = 3;

    /**
     * Collection order attribute
     * @var string
     */
    const ORDER_BY = 'added_at';

    /**
     * Collection order direction
     * @var string
     */
    const SORT_DIRECTION = 'desc';

    /**
     * Overrides getItems to update sort order, order direction & page size
     * @return array
     */
    protected function getItems()
    {
        $this->view->loadLayout();

        $collection = $this->wishlistHelper->getWishlistItemCollection();
        $collection->clear()->setPageSize(self::SIDEBAR_ITEMS_NUMBER)
            ->setInStockFilter(true)->setOrder(self::ORDER_BY, self::SORT_DIRECTION);

        $items = [];
        foreach ($collection as $wishlistItem) {
            $items[] = $this->getItemData($wishlistItem);
        }
        return $items;
    }
}
