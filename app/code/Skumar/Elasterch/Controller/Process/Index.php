<?php
namespace Skumar\Elasterch\Controller\Process;

class Index extends \Skumar\Elasterch\Controller\Index
{
	public function execute() {
		$queryString = $this->getRequest()->getParam('query_string');
		$response =  $this->_elasticClient->searchDocument('product', 30, 0, $queryString);
		$hits = $response['hits']['hits'];

		$html = '<div class="elasterch-result">';
		foreach($hits as $hit) {
			$html .= '<div class="elasterch-product"><div class="elasterch-product-name"><a href="'.$hit['_source']['product_url'].'" target="_blank">'.$hit['_source']['name'].'</a></div>';
			$html .= '<div class="elasterch-product-price">'.$this->_priceCurrency->convertAndFormat($hit['_source']['price']).'</div></div>';
		}
		$html .= '</div>';

		echo $html;
	}
}