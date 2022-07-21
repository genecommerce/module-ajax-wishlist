<?php

declare(strict_types=1);

namespace Gene\Wishlist\Controller\Item;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;

class Add extends Action
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * Add constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param ResultFactory $resultFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        ResultFactory $resultFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        Validator $formKeyValidator,
        UrlInterface $url
    ) {
        $this->customerSession = $customerSession;
        $this->wishlistRepository= $wishlistRepository;
        $this->productRepository = $productRepository;
        $this->resultFactory = $resultFactory;
        $this->jsonFactory = $jsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->url = $url;
        parent::__construct($context);
    }

    public function execute()
    {
        /**
         * Check FormKey is Valid
         */
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $refererUrl = $this->_redirect->getRefererUrl();
            $this->messageManager->addErrorMessage(__('Invalid Form Key. Please refresh the page.'));
            $jsonData = [
                'result' => [
                    'status' => 403,
                    'redirect' => 1,
                    'message' => 'Invalid Form Key',
                    'url' => $refererUrl
                ]
            ];
            $result = $this->jsonFactory->create()->setData($jsonData);
            return $result;
        }

        /**
         * Check if customer logged in
         */
        $customerId = $this->customerSession->getCustomer()->getId();
        if (!$customerId) {
            $this->messageManager->addErrorMessage(__('You must login or register to add items to your wishlist.'));
            $jsonData = [
                'result' => [
                    'status' => 403,
                    'redirect' => 1,
                    'message' => 'Customer not logged in.',
                    'url' => $this->url->getUrl('customer/account/login')
                ]
            ];
            $result = $this->jsonFactory->create()->setData($jsonData);
            return $result;
        }

        /**
         * Check if product exists
         */
        $productId = $this->getRequest()->getParam('productId');
        try {
            $product = $this->productRepository->getById($productId['data']['product']);
        } catch (\Exception $e) {
            $product = null;
        }

        /* return empty array if no product */
        if (!$product || !$product->isVisibleInCatalog()) {
            $jsonData = ['result' => []];
            $result = $this->jsonFactory->create()->setData($jsonData);
            return $result;
        }

        /**
         * Get Wishlist
         */
        $wishlist = $this->wishlistRepository->create()->loadByCustomerId($customerId, true);

        /**
         * Update Wishlist
         */
        $wishlist->addNewItem($product);
        $wishlist->save();

        /**
         * Return result
         */
        $jsonData = [
            'result' => [
                'status' => 200,
                'redirect' => 0,
                'message' => 'Added to wishlist'
            ]
        ];
        $result = $this->jsonFactory->create()->setData($jsonData);
        return $result;
    }
}
