<?php
namespace Skumar\Elasterch\Controller;

abstract class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Skumar\Elasterch\Connecter\ElasticClient
     */
    protected $_elasticClient;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Skumar\Elasterch\Connecter\ElasticClient $elastic
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Skumar\Elasterch\Connecter\ElasticClient $elastic,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->_elasticClient = $elastic;
        $this->_logger = $logger;
        parent::__construct($context);
    }
}