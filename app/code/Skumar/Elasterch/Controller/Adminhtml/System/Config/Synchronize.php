<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Skumar\Elasterch\Controller\Adminhtml\System\Config;

use \Magento\Catalog\Model\Product\Visibility;

class Synchronize extends \Magento\Backend\App\Action
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
     *@param \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;


    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Skumar\Elasterch\Connecter\ElasticClient $elastic
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Skumar\Elasterch\Connecter\ElasticClient $elastic,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_elasticClient = $elastic;
        $this->_productFactory = $productFactory;
        $this->_imageHelper = $imageHelper;
        $this->_logger = $logger;
        parent::__construct($context);
    }


    /**
     * Synchronize products
     *
     * @return void
     */
    public function execute()
    {
        if($this->getRequest()->getParam('flush_index')) {
            $this->_elasticClient->deleteIndex();
            $this->_elasticClient->initIndex();
        }

        $page = $this->getRequest()->getParam('page_num');
        $nextPage = $page + 1;

        $collection = $this->_productFactory->create()->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('thumbnail')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('product_url')
            ->setPage($page, 50);
        $collection->setVisibility(array(Visibility::VISIBILITY_IN_SEARCH, Visibility::VISIBILITY_BOTH));

        $results = $collection->load();
        $totalPage = $results->getLastPageNumber();

        foreach($results as $product) {
            $params = array();

            $params['body']  = array(                                         // Document params
                'id' => $product->getId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'thumbnail' => $product->getData('thumbnail'),
                'price' => $product->getData('price'),
                'product_url' => $product->getProductUrl()
            );

            $this->_elasticClient
                ->addDocument('product', $product->getId(), $params);        // Add/Update document                
        }

        if($nextPage <= $totalPage) {
            echo json_encode(array('has_next_page' => 1, 'page_num' => $nextPage, 'progress' => ($page/$totalPage)*100));
        } else {
            echo json_encode(array('has_next_page' => 0));
        }
    }
}
