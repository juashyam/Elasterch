<?php
namespace Skumar\Elasterch\Connecter;

class ElasticClient
{
    /**
     * @var \Elasticsearch\ClientBuilder
     */
    protected $_elasticClient;


    /**
     * @param \Elasticsearch\ClientBuilder $elastic
     */
    public function __construct(\Elasticsearch\ClientBuilder $elastic) {
        $this->_elasticClient = $this->_buildElasticClient($elastic);
    }


    /**
     * Build a elasticsearch client
	 *
	 * @return \Elasticsearch\ClientBuilder
	 */
    protected function _buildElasticClient($elastic) {
		return $elastic::create()->setHosts(["http://127.0.0.1:9200"])->build();
    }


    /**
     * Get elasticsearch client
     *
     * @return \Elasticsearch\ClientBuilder
     */
    public function getElasticClient() {
    	return $this->_elasticClient;
    }


    public function isDocumentExist($type, $id) {
        try {
            $params['index'] = 'catalog';
            $params['type']  = $type;
            $params['id'] = $id;
            $response = $this->_elasticClient->get($params);
            return $response['found'];
        } catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $e) {
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

        if($this->isDocumentExist($type, $params['id'])) {
            $this->updateDocument($type, $params['id'], $temp_params['body']);
        } else {
	        $response = $this->_elasticClient->index($params);
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
        }
    }
}