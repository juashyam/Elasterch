<?php
namespace Skumar\Elasterch\Controller\Index;

class Index extends \Skumar\Elasterch\Controller\Index
{
  public function execute() {
	$this->_view->loadLayout();
	$this->_view->renderLayout();
  }
}