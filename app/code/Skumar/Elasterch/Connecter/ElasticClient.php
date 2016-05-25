<?php
namespace Skumar\Elasterch\Connecter;

class ElasticClient
{
    /**
     * Host Configuration.
     */
    const HOSTS = ["http://127.0.0.1:9200"];

    /**
     * Host Configuration.
     */
    const PRODUCT_DOCUMENT_PARAMS = array('id', 'name', 'sku', 'price', 'product_url');

    /**
     * @var \Elasticsearch\ClientBuilder
     */
    protected $_elasticClient;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;


    /**
     * @param \Elasticsearch\ClientBuilder $elastic
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Elasticsearch\ClientBuilder $elastic, \Psr\Log\LoggerInterface $logger) {
        $this->_logger = $logger;
        $this->_elasticClient = $this->_buildElasticClient($elastic);
    }


    /**
     * Build a elasticsearch client
	 *
	 * @return \Elasticsearch\ClientBuilder
	 */
    protected function _buildElasticClient($elastic) {
        try {
    		$client = $elastic::create()                  // Instantiate a new ClientBuilder
                ->setHosts(self::HOSTS)                   // Set the hosts
                ->setLogger($this->_logger)               // Set the logger with a default logger
                ->build();

            return $client;
        } catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $e) {
            $this->_logger->error($e);
        }
    }


    /**
     * Get elasticsearch client
     *
     * @return \Elasticsearch\ClientBuilder
     */
    public function getElasticClient() {
    	return $this->_elasticClient;
    }


    /**
     * Get product params
     */
    public function getProductParams() {
        return self::PRODUCT_DOCUMENT_PARAMS;
    }


    /**
     * Check document exists or not by ID
     */
    public function isDocumentExist($type, $id) {
        try {
            $params['index'] = 'catalog';
            $params['type']  = $type;
            $params['id'] = $id;
            $params['client'] = array('ignore' => array(400, 404));         // Ignoring exceptions

            $response = $this->_elasticClient->get($params);
            return ($response['found'] > 0) ? true : false;
        } catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $e) {
            $this->_logger->error($e);
            return false;
        }
    }


    /**
     * Add document to index
     */
    public function addDocument($type, $id, array $params) {
        $temp_params = $params;

        $params['index'] = 'catalog';
        $params['type']  = $type;
        $params['id'] = $type . '-' . $id;

        try {
            if($this->isDocumentExist($type, $params['id'])) {
                $this->updateDocument($type, $params['id'], $temp_params['body']);
            } else {
    	        $response = $this->_elasticClient->index($params);
            }
        } catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $e) {
            $this->_logger->error($e);
        }
    }


    /**
     * Update existing document
     */
    public function updateDocument($type, $id, array $data) {
        try {
            $params['index'] = 'catalog';
            $params['type']  = $type;
            $params['id'] = $id;
            $response = $this->_elasticClient->get($params);

            foreach ($data as $param_key => $param_value) {
                $response['_source'][$param_key] = $param_value;
            }

            $params['body']['doc'] = $response['_source'];
            $this->_elasticClient->update($params);
        } catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $e) {
            $this->_logger->error($e);
        }
    }
}