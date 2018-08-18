<?php


namespace Ndsl\Orderexport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Orderexport extends Command
{

    private $state;
    const NAME_ARGUMENT = "name";
    const NAME_OPTION = "option";
	const ENCLOSURE = '"';
    const DELIMITER = ',';

    public function __construct(\Magento\Framework\App\State $state) {
        $this->state = $state;
        parent::__construct();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND); 
        $name = $input->getArgument(self::NAME_ARGUMENT);
        $option = $input->getOption(self::NAME_OPTION);

        //$output->writeln($option);
        //$output->writeln($name);

        $orderId = $input->getArgument(self::NAME_ARGUMENT);
        if (empty($orderId)) {
            $output->writeln('Please pass one argument.');
        } else {
            try {

                $orderSearchParams = [
                    'increment_id' => "000000005",
                    'customer_email' => "roni_cost@example.com"
                ];
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $resultPage = $objectManager->create('\Ndsl\Orderexport\Model\Orderexport')->getOrderDataArray($orderSearchParams);
                echo json_encode($resultPage);die;

            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }
        }
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
    }
}
