<?php
namespace Ndsl\Orderexport\Model\ResourceModel;

class Orderexport extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('otest', 'otest_id');
    }
}
?>