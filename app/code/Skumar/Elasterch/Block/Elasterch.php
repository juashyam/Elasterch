<?php
namespace Skumar\Elasterch\Block;

use Magento\Framework\View\Element\Template;

class Elasterch extends Template
{
    /**
     *@param \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        $this->_formKey = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * Returns action url to process search query
     *
     * @return string
     */
    public function getElasterchProcessUrl()
    {
        return $this->getUrl('elasterch/process');
    }

    /**
     * Returns form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->_formKey->getFormKey();
    }    
}