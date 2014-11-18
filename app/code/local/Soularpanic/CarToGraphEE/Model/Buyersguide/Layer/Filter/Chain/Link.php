<?php
class Soularpanic_CarToGraphEE_Model_Buyersguide_Layer_Filter_Chain_Link
    extends Mage_Catalog_Model_Layer_Filter_Abstract {

    public function _construct() {
        parent::_construct();
    }

    public function getRequestVar() {
        $reqVar = parent::getRequestVar();
        if (!$reqVar) {
            $this->setRequestVar($this->getId());
        }
        return parent::getRequestVar();
    }

    public function evaluate($command, $chain, $request, $filterBlock) {
        $selection = $request->getParam($this->getRequestVar());
        if (!$selection) {
            return $command;
        }
        else {
            $newCommand = false;
            foreach ($this->getOptions() as $option) {
                if ($option->getId() === $selection) {
                    $newCommand = $option->getAction();
                }
            }
            return $newCommand;
        }
    }

}