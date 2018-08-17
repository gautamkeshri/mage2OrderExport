<?php
namespace Ndsl\Orderexport\Model;

class Orderexport extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ndsl\Orderexport\Model\ResourceModel\Orderexport');
    }
}
?>