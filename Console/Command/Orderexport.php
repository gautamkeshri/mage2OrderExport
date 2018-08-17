<?php


namespace Ndsl\Orderexport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Orderexport extends Command
{

    const NAME_ARGUMENT = "name";
    const NAME_OPTION = "option";
	const ENCLOSURE = '"';
    const DELIMITER = ',';

    
    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $name = $input->getArgument(self::NAME_ARGUMENT);
		
		
        $option = $input->getOption(self::NAME_OPTION);

        //$output->writeln($option);
        //$output->writeln($name);

        $orderId = $input->getArgument(self::NAME_ARGUMENT);
        if (empty($orderId)) {
            $output->writeln('Please pass one argument.');
        } else {
            try {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                //$order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
                //$order = $objectManager->create('\Ndsl\Orderexport\Model\Orderexport')->load($orderId);
                //$output->writeln($order->getData());
                print_r($order->getData());
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }
        }

        /*
		//Working Order detail expo code
		print_r($order->getData());
		print_r($order->getBillingAddress()->getData());
		print_r($order->getShippingAddress()->getData());
		foreach ($order->getAllItems() as $item) {
			print_r($item->getData());
        }
        */
        /*
		$customerId = $order->getCustomerId();
		$customer = $objectManager->create('\Magento\Customer\Model\Customer')->load($customerId);
		print_r($customer->getData()); 
		*/
		
		//$productId = 1;
		//$product = $objectManager->create('\Magento\Catalog\Model\Product')->load($productId);
		//print_r($product->getData());
        //$output->writeln($order);
        //$output->writeln("Hello " . $name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("ndsl_order:export");
        $this->setDescription("order export");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);
        parent::configure();
    }
	
	public function exportOrders($orders) 
    {
		if (!file_exists($this->_directoryList->getRoot().'/pub/media/orderexport')) {
            mkdir($this->_directoryList->getRoot().'/pub/media/orderexport', 0777, true);
        }
		$fileName = $this->_directoryList->getRoot().'/pub/media/orderexport/orderexport'.$todayDate.'.csv';
         
        $fp = fopen($fileName, 'w');

        $this->writeHeadRow($fp);
		/*
        foreach ($orders as $order) {
			$order = $objectManager->create('\Magento\Sales\Model\Order')->load($order);
            $this->writeOrder($order, $fp);
        }
		*/
        fclose($fp);

        return $fileName;
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
			'Coupon Code'
    	);
    }
	
	
	protected function writeOrder($order, $fp) 
    {
		/*
        $common = $this->getCommonOrderValues($order);

        $orderItems = $order->getItemsCollection();
        $itemInc = 0;
        foreach ($orderItems as $item)
        {
            if (!$item->isDummy()) {
                $record = array_merge($common, $this->getOrderItemValues($item, $order, ++$itemInc));
                fputcsv($fp, $record, self::DELIMITER, self::ENCLOSURE);
            }
        }
		*/
    }
}
