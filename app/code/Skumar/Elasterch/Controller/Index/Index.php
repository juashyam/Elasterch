<?php
namespace Skumar\Elasterch\Controller\Index;

class Index extends \Skumar\Elasterch\Controller\Index
{
	public function execute() {
		/*$this->_view->loadLayout();
		$this->_view->renderLayout();*/

		/*$params = [
		    'index' => 'catalog',
		    'type' => 'product',
		    'size' => 1000,
		    'body' => [
		        'query' => [
		            "match_all" => []
		        ]
		    ]
		];


		$results = $this->_elasticClient->getelasticClient()->search($params);

		echo '<pre>'; print_r($results);*/

		$params = [
		    'index' => 'catalog',
		    'type' => 'product'
		];
		echo $this->_elasticClient->getelasticClient()->count($params)["count"];

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