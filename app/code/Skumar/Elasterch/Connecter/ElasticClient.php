<?php
namespace Skumar\Elasterch\Connecter;

class ElasticClient
{
    /**
     * Host Configuration.
     */
    const HOSTS = ["http://shyam:shyamkumar@10.16.16.160:3001"];

    /**
     * Index name
     */
    const INDEX = "catalog";

    /**
     * DOcument parameters
     */
    const PRODUCT_DOCUMENT_PARAMS = array('id', 'name', 'sku', 'thumbnail', 'price', 'product_url');

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

            $indexParams['index']  = self::INDEX;

            /*$indexParams = [
                'index' => self::INDEX,
                'body' => [
                    'settings' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 0,
                        'analysis' => [
                            'tokenizer' => [
                                'my_ngram_tokenizer' => [
                                    'type' => 'nGram',
                                    'min_gram' => 1,
                                    'max_gram' => 15,
                                    'token_chars' => ['letter', 'digit']
                                ]
                            ],
                            'analyzer' => [
                                'my_ngram_analyzer' => [
                                    'tokenizer' => 'my_ngram_tokenizer',
                                    'filter' => 'lowercase',
                                ]
                            ],
                        ]
                    ],
                    'mappings' => [
                        '_default_' => [
                            'properties' => [
                                'id' => [
                                    'type' => 'digit',
                                    'index' => 'not_analyzed',
                                ],
                                'name' => [
                                    'type' => 'string',
                                    'analyzer' => 'my_ngram_analyzer',
                                    'term_vector' => 'yes',
                                    'copy_to' => 'combined'
                                ],
                                'sku' => [
                                    'type' => 'string',
                                    'analyzer' => 'my_ngram_analyzer',
                                    'term_vector' => 'yes',
                                    'copy_to' => 'combined'
                                ],
                                'image' => [
                                    'type' => 'string',
                                    'index' => 'not_analyzed',
                                ],
                                'price' => [
                                    'type' => 'digit',
                                    'index' => 'not_analyzed',
                                ],
                                'product_url' => [
                                    'type' => 'string',
                                    'index' => 'not_analyzed',
                                ],
                            ]
                        ],
                    ]
                ]
            ];*/

            if(!$client->indices()->exists($indexParams)) {
                $client->indices()->create($indexParams);
            }

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
     * Init index
     */
    public function initIndex() {
        $indexParams['index']  = self::INDEX;
        if(!$this->_elasticClient->indices()->exists($indexParams)) {
            $this->_elasticClient->indices()->create($indexParams);
        }
    }


    /**
     * Delete index
     */
    public function deleteIndex() {
        $indexParams['index']  = self::INDEX;
        $this->_elasticClient->indices()->delete($indexParams);
    }


    /**
     * Count documents
     */
    public function getDocumentCount($type = 'product') {
        $params = [
            'index' => self::INDEX,
            'type' => $type
        ];
        return $this->_elasticClient->count($params)["count"];
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
            $params['index'] = self::INDEX;
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
     * Get all documents
     */
    public function getDocuments($type = 'product', $size = null) {
        try {
            if(is_null($size)) {
                $size = $this->getDocumentCount();
            }

            $params = [
                'index' => self::INDEX,
                'type' => $type,
                'size' => $size,
                'body' => [
                    'query' => [
                        "match_all" => []
                    ]
                ]
            ];

            $response = $this->_elasticClient->search($params);
            return $response;
        } catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $e) {
            $this->_logger->error($e);
            return false;
        }
    }


    /**
     * Search document
     */
    public function searchDocument($type, $size, $from, $queryString) {
        try {
            $params['index'] = self::INDEX;
            $params['type']  = $type;
            $params['size']  = $size;
            $params['from']  = $from;

            /*$params['body']['query']['wildcard']['name'] = '*'.$queryString.'*';*/

            $params['body']['query']['multi_match']['query'] = $queryString;
            $params['body']['query']['multi_match']['type'] = 'best_fields';
            $params['body']['query']['multi_match']['fields'] = ['name'];

            $params['body']['sort'] = ['_score'];
            $params['client'] = array('ignore' => array(400, 404));         // Ignoring exceptions

            $response = $this->_elasticClient->search($params);
            return $response;
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

        $params['index'] = self::INDEX;
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
            $params['index'] = self::INDEX;
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


    /**
     * Delete document
     */
    public function deleteDocument($type, array $params) {
        $params['index'] = self::INDEX;
        $params['type']  = $type;

        try {
            if($this->isDocumentExist($type, $params['id'])) {
                $this->_elasticClient->delete($params);
            }
        } catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $e) {
            $this->_logger->error($e);
        }
    }    
}