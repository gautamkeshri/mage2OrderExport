<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ndsl\Orderexport\Controller\Adminhtml\Order;

//use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\Filesystem\DirectoryList;
class MassExport extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
  const ENCLOSURE = '"';
  const DELIMITER = ',';

  protected $_directoryList;
  public $_resource;
  private $deploymentConfig;
  private $objectManager;
  public function __construct(Context $context,
  ResourceConnection $resource,
  Filter $filter, CollectionFactory $collectionFactory,DeploymentConfig $deploymentConfig,DirectoryList $directory_list)
    {

    $this->_resource = $resource;
    parent::__construct($context , $filter);
    $this->deploymentConfig = $deploymentConfig;
    $this->collectionFactory = $collectionFactory;
    $this->_directoryList = $directory_list;
	$this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();

  }
    /**
     * Cancel selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {

        if (!file_exists($this->_directoryList->getRoot().'/pub/media/orderexport')) {
            mkdir($this->_directoryList->getRoot().'/pub/media/orderexport', 0777, true);
        }

        $todayDate = date('Y_m_d_H_i_s', time());
        $fileName = $this->_directoryList->getRoot().'/pub/media/orderexport/orderexport'.$todayDate.'.csv';


        $fp = fopen($fileName, 'w');
		$this->writeHeadRow($fp);

		$countOrderExport = 0;
        foreach ($collection->getItems() as $_order) {

          $orderId = $_order->getId();
          if ($orderId) {
			$order = $this->objectManager->create('\Magento\Sales\Model\Order')->load($_order->getId());
            $this->writeOrder($order, $fp);
            $incId = $order->getIncrementId();
			$countOrderExport++;
          }
        }
		fclose($fp);

        $this->downloadCsv($fileName);
        $this->messageManager->addSuccess(__('We Exported %1 order(s).', $countOrderExport));

        //$resultRedirect = $this->resultRedirectFactory->create();
        //$resultRedirect->setPath('sales/*/');
        //return $resultRedirect;
    }


    public function downloadCsv($file)
    {

        if (file_exists($file)) {
            //set appropriate headers
            header('Content-Description: File Transfer');
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();flush();
            readfile($file);
        }
    }

	protected function writeHeadRow($fp)
    {
        fputcsv($fp, $this->getHeadRowValues(), self::DELIMITER, self::ENCLOSURE);
    }

	protected function getHeadRowValues()
    {
        return array(
            'Order Number',
            'Order Date',
            'Order Status',
            'Order Purchased From',
            'Order Payment Method',
            'Order Shipping Method',
            'Order Subtotal',
            'Order Tax',
            'Order Shipping',
            'Order Discount',
            'Order Grand Total',
        	'Order Paid',
            'Order Refunded',
            'Order Due',
            'Total Qty Items Ordered',
            'Customer Name',
            'Customer Email',
            'Shipping Name',
            'Shipping Company',
            'Shipping Street',
            'Shipping Zip',
            'Shipping City',
        	'Shipping State',
            'Shipping State Name',
            'Shipping Country',
            'Shipping Country Name',
            'Shipping Phone Number',
    		'Billing Name',
            'Billing Company',
            'Billing Street',
            'Billing Zip',
            'Billing City',
        	'Billing State',
            'Billing State Name',
            'Billing Country',
            'Billing Country Name',
            'Billing Phone Number',
            'Order Item Increment',
    		'Item Name',
            'Item Status',
            'Item SKU',
            'Item Options',
            'Item Original Price',
    		'Item Selling Price',
            'Item Qty Ordered',
        	'Item Qty Invoiced',
        	'Item Qty Shipped',
        	'Item Qty Canceled',
            'Item Qty Refunded',
            'Item Tax',
            'Item Discount',
    		'Item Total',
            'Coupon Code',
			'GST Number',
			'premium_care'
    	);
    }

	protected function writeOrder($order, $fp)
    {
        $common = $this->getCommonOrderValues($order);


        $orderItems = $order->getAllVisibleItems();
        $itemInc = 0;
		$item = "";
        foreach ($orderItems as $item)
        {
          //  if (!$item->isDummy()) {
                $record = array_merge($common, $this->getOrderItemValues($item, $order, ++$itemInc));
                fputcsv($fp, $record, self::DELIMITER, self::ENCLOSURE);
          //  }
        }

    }

	protected function getCommonOrderValues($order)
    {
		$shippingAddress = !$order->getIsVirtual() ? $order->getShippingAddress() : null;
		$billingAddress = $order->getBillingAddress();

		$payment = $order->getPayment();
		$method = $payment->getMethodInstance();
		$methodTitle = $method->getTitle();
		$total_item_qty = $this->getTotalQtyItemsOrdered($order);

		$priceHelper = $this->objectManager->create('Magento\Framework\Pricing\Helper\Data');
		$objDate = $this->objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
		$country_name = $this->objectManager->create('\Magento\Directory\Model\Country');

        return array(
            $order->getIncrementId(),
            $objDate->date(new \DateTime($order->getCreatedAt()))->format('m-d-Y'),
            $order->getStatus(),
            $order->getData('store_name'),
            $methodTitle,
            $order->getData('shipping_method'),
			number_format($order->getData('subtotal'), 2, '.',''),
			number_format($order->getData('tax_amount'), 2, '.',''),
			number_format($order->getData('shipping_amount'), 2, '.',''),
			number_format($order->getData('discount_amount'), 2, '.',''),
			number_format($order->getData('grand_total'), 2, '.',''),
			number_format($order->getData('total_paid'), 2, '.',''),
			number_format($order->getData('total_refunded'), 2, '.',''),
			number_format($order->getData('total_due'), 2, '.',''),
            $total_item_qty,
            $order->getData('customer_firstname')." ".$order->getData('customer_lastname'),
            $order->getData('customer_email'),
            $shippingAddress ? $shippingAddress->getName() : '',
            $shippingAddress ? $shippingAddress->getData("company") : '',
            $shippingAddress ? $shippingAddress->getData("street") : '',
            $shippingAddress ? $shippingAddress->getData("postcode") : '',
            $shippingAddress ? $shippingAddress->getData("city") : '',
            $shippingAddress ? $shippingAddress->getRegion() : '',
            $shippingAddress ? $shippingAddress->getRegion() : '',
            $shippingAddress ? $shippingAddress->getdata("country_id") : '',
            $shippingAddress ? $shippingAddress->getData("country_id") : '',
            $shippingAddress ? $shippingAddress->getData("telephone")."/".$shippingAddress->getData("fax")  : '',
            $order->getData('customer_firstname')." ".$order->getData('customer_lastname'),
            $billingAddress->getData("company"),
            $billingAddress->getData("street"),
            $billingAddress->getData("postcode"),
            $billingAddress->getData("city"),
            $billingAddress->getRegionCode(),
            $billingAddress->getRegion(),
            $billingAddress->getData("country_id"),
            $country_name->load($billingAddress->getData("country_id"))->getName(),
            $billingAddress->getData("telephone")."/".$billingAddress->getData("fax")
        );

    }

	protected function getOrderItemValues($item, $order, $itemInc=1)
    {
		$custom_option = "";
		if($item->hasProductOptions()){
			if (array_key_exists("options",$item->getData('product_options'))){
				$option_coll = $item->getData('product_options')['options'];
				foreach ($option_coll as $cptions) {
					$custom_option .= strip_tags($cptions['label']).";";
				}
			}
		}
		//$product = $this->objectManager->get('Magento\Catalog\Model\Product')->load($item->getId());
		//$offerinfo = $product->getResource()->getAttribute("offer")->getFrontend()->getValue($product);
		//unset($product);
        return array(
            $itemInc,
            $item->getName(),
            $item->getStatus(),
            $item->getSku(),
            $custom_option,
			$item->getOriginalPrice(),
            $item->getPrice(),
            (int)$item->getQtyOrdered(),
            (int)$item->getQtyInvoiced(),
            (int)$item->getQtyShipped(),
            (int)$item->getQtyCanceled(),
        	(int)$item->getQtyRefunded(),
			$order->getData('tax_amount'),
			abs($item->getData('discount_amount')),
			$item->getData('row_total') - $item->getDiscountAmount(),
			$order->getCouponCode(),
			$order->getBillingAddress()->getData('vat_id')
        );


    }

	protected function getTotalQtyItemsOrdered($order)
	{

        $qty = 0;

        $orderedItems = $order->getAllVisibleItems();
        foreach ($orderedItems as $qitem)
        {
            //if (!$item->isDummy()) {
                $qty += (int)$qitem->getQtyOrdered();
            //}
        }

        return $qty;

    }
}
