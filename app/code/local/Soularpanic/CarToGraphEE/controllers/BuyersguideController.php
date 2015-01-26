<?php
class Soularpanic_CarToGraphEE_BuyersguideController
    extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }


    public function updatecarselectionAction() {
        $selections = Mage::app()->getRequest()->getParam('car');
        $available = Mage::helper('cartographee/car')->getFilteredCarProperties($selections);
        $data = Mage::helper('core')->jsonEncode($available);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($data);
    }
}