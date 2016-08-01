<?php
namespace Skumar\Elasterch\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Catalog\Model\Product\Visibility;

class ProductAttributeUpdateBefore implements ObserverInterface
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


    /**
     * Observe mass attribute update event
     * and add/delete document for each product based on visibility
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $updateFlag = false;
        $massUpdateAttributeData = $observer->getAttributesData();
        $massUpdateAtributes = array_keys($massUpdateAttributeData);

        $elasterchProductParams = $this->_elasticClient->getProductParams();
        $elasterchProductParams[] = 'visibility';
        foreach ($massUpdateAtributes as $temp) {
            if(in_array($temp, $elasterchProductParams)) {
                $updateFlag = true;
                break;
            }
        }

        $collection = $this->_productFactory->create()->getCollection()->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'thumbnail'
        )->addAttributeToSelect(
            'price'
        )->addAttributeToSelect(
            'visibility'
        )->addAttributeToSelect(
            'product_url'
        );
        $collection->addIdFilter($observer->getProductIds());

        if($updateFlag) {
            if(in_array('visibility', $massUpdateAtributes)) {
                if(in_array($massUpdateAttributeData['visibility'], array(Visibility::VISIBILITY_IN_SEARCH, Visibility::VISIBILITY_BOTH))) {
                    $this->_prepareAddDocument($collection, $massUpdateAttributeData);
                } else {
                    $this->_prepareDeleteDocument($collection);
                }
            } else {
                $this->_prepareAddDocument($collection, $massUpdateAttributeData);
            }
        }
    }


    /**
     * Prepare parameters to add document
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    protected function _prepareAddDocument($collection, $massUpdateAttributeData) {
        foreach($collection as $product) {
            $params = array();
            $tmp_params = array(                                         // Document params
                'id' => $product->getId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'thumbnail' => $product->getData('thumbnail'),
                'price' => $product->getData('price'),
                'product_url' => $product->getProductUrl()
            );
            $params['body'] = array_merge($tmp_params, $massUpdateAttributeData);

            $this->_elasticClient
                ->addDocument('product', $product->getId(), $params);        // Add document                
        }
    }


    /**
     * Prepare parameters to delete document
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    protected function _prepareDeleteDocument($collection) {
        foreach($collection as $product) {
            $params = [
                'id' => 'product-'.$product->getId()
            ];

            $this->_elasticClient
                ->deleteDocument('product', $params);        // Add document
        }
    }    
}