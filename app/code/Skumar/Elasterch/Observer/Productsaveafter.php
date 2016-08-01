<?php
namespace Skumar\Elasterch\Observer;

use Magento\Framework\Event\ObserverInterface;

class Productsaveafter implements ObserverInterface
{
    /**
     * @var \Skumar\Elasterch\Connecter\ElasticClient
     */
    protected $_elasticClient;

    /**
     * @var \Psr\Log\LoggerInterface
     */
	protected $_logger;


    /**
     * @param \Skumar\Elasterch\Connecter\ElasticClient $elastic
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
    	\Skumar\Elasterch\Connecter\ElasticClient $elastic,
    	\Psr\Log\LoggerInterface $logger
    ) {
		$this->_elasticClient = $elastic;
    	$this->_logger = $logger;
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$product = $observer->getProduct();
		$params['body']  = array(
			'id' => $product->getId(),
			'name' => $product->getName(),
			'sku' => $product->getSku(),
			'price' => $product->getPrice(),
			'product_url' => $product->getProductUrl()
		);

		$this->_elasticClient->addDocument('product', $product->getId(), $params);
    	//echo '<pre>'; print_r($observer->getProduct()->getData()); exit;
    	//$this->_logger->debug('Skumar Elasterch :: Product Data', $observer->getProduct()->getData());
    }
}