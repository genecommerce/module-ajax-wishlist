<?php

declare(strict_types=1);

namespace Gene\Wishlist\Controller\Item;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Wishlist\Model\Item;
use Gene\Wishlist\Model\Wishlist;

class Remove extends Action implements HttpPostActionInterface
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var WishlistProviderInterface
     */
    private $wishlistProvider;

    /**
     * Remove constructor.
     * @param Context $context
     * @param Validator $validator
     * @param ItemFactory $itemFactory
     * @param WishlistProviderInterface $wishlistProvider
     */
    public function __construct(
        Context $context,
        Validator $validator,
        ItemFactory $itemFactory,
        WishlistProviderInterface $wishlistProvider
    ) {
        $this->validator = $validator;
        $this->itemFactory = $itemFactory;
        $this->wishlistProvider = $wishlistProvider;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $request = $this->getRequest();
        $data = [];
        if (!$request->isAjax()) {
            throw new NotFoundException(__('Page not found.'));
        }
        if (!$this->validator->validate($request)) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        }
        $id = $request->getParam('item');

        if ($id) {
            /** @var Item $item */
            $item = $this->itemFactory->create();
            $item->load($id);
            try {
                /** @var Wishlist $wishlist */
                $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
                $data = [
                    'deleted' => $id,
                    'wishlist' => $wishlist->getWishlistId()
                ];

                $item->delete();
                $wishlist->save();
            } catch (\Exception $e) {
                $data['error'] = $e->getMessage();
            }
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($data);
    }
}
