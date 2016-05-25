<?php
namespace Skumar\Elasterch\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var \Skumar\Elasterch\Connecter\ElasticClient
     */
    protected $_elasticClient;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
	protected $_logger;


    /**
     * @param \Skumar\Elasterch\Connecter\ElasticClient $elastic
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
    	\Skumar\Elasterch\Connecter\ElasticClient $elastic,
        \Magento\Catalog\Model\ProductFactory $productFactory,
    	\Psr\Log\LoggerInterface $logger
    ) {
		$this->_elasticClient = $elastic;
        $this->_productFactory = $productFactory;
    	$this->_logger = $logger;
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$temp = $observer->getProduct();
        $product = $this->_productFactory->create();
        $product->load($temp->getId());

		$params['body']  = array(                                         // Document params
			'id' => $product->getId(),
			'name' => $product->getName(),
			'sku' => $product->getSku(),
			'price' => $product->getPrice(),
			'product_url' => $product->getProductUrl()
		);

		$this->_elasticClient
            ->addDocument('product', $product->getId(), $params);        // Add document
    }
}