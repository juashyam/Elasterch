<?php
namespace Skumar\Elasterch\Controller\Index;

class Index extends \Skumar\Elasterch\Controller\Index
{
	public function execute() {
		$this->_view->loadLayout();
		$this->_view->renderLayout();

		/*$params = [
		    'index' => 'catalog',
		    'type' => 'product',
		    'size' => $this->_elasticClient->countDocument(),
		    'body' => [
		        'query' => [
		            "match_all" => []
		        ]
		    ]
		];


		$results = $this->_elasticClient->getElasticClient()->search($params);

		echo '<pre>'; print_r($results);*/



		// $results = $this->_elasticClient->getElasticClient()->getDocumentCount();
        /*$params = [
            'index' => 'catalog',
            'type' => 'product'
        ];

		echo '<pre>'; print_r(json_encode($this->_elasticClient->getDocuments()['hits']['hits'])); exit;*/

		/*$queryString = '*oga*';
		$response =  $this->_elasticClient->searchDocument('product', 100, 0, $queryString);
		$hits = $response['hits']['hits'];

		$html = '<div>';
		foreach($hits as $hit) {
			$html .= '<p><a href="'.$hit['_source']['product_url'].'" target="_blank">'.$hit['_source']['name'].'</a></p>';
			$html .= '<p>'.$this->_priceCurrency->convertAndFormat($hit['_source']['price']).'</p>';
		}
		$html .= '</div>';

		echo $html;*/
	}
}