<?php
namespace Ndsl\Orderexport\Model;

class Orderexport extends \Magento\Framework\Model\AbstractModel
{
    protected $objectManager;
    protected $orderRepository;
    protected $paymentHelper;
    protected $shippingHelper;
    protected $pricingHelper;
    protected $orderDataArray;
    protected $orderCollection;
	protected $searchCriteriaBuilder;
    /**
     * Initialize resource model
     *
     * @return void
     */
    public function __construct(
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		\Magento\Payment\Helper\Data $paymentHelper,
		\Magento\Shipping\Helper\Data $shippingHelper,
		\Magento\Framework\Pricing\Helper\Data $pricingHelper,
		\Magento\Framework\Registry $data,
		\Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
	)
    {
        $this->objectManager = $objectManager;
        $this->orderRepository = $orderRepository;
        $this->paymentHelper = $paymentHelper;
        $this->shippingHelper = $shippingHelper;
        $this->pricingHelper = $pricingHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context, $data);
        $this->_init('Ndsl\Orderexport\Model\ResourceModel\Orderexport');
    }

    /**
     * @param $orderSearchParams
     *
     * @return mixed
     */
    public function getOrderDataArray($orderSearchParams)
    {
        $this->_prepareOrderCollection($orderSearchParams);
        $this->_setOrderDataArray();

        return $this->orderDataArray;
    }

    /**
     * Get order collection filtered by order id and email.
     * @param $orderSearchParams
     */
    private function _prepareOrderCollection($orderSearchParams)
    {
        $search_criteria = $this->searchCriteriaBuilder
            ->addFilter("increment_id", $orderSearchParams['increment_id'], "eq")
            //->addFilter("customer_email", $orderSearchParams['customer_email'], "eq")
            ->create();

        $this->orderCollection = $this->orderRepository
            ->getList($search_criteria);
    }

    /**
     * Prepare orderDataArray with order info.
     */
    private function _setOrderDataArray()
    {
        foreach ($this->orderCollection->getItems() as $order){
            $this->_prepareOrderStatusArray($order);
            $this->_prepareOrderPaymentArray($order);
            $this->_prepareOrderProductsArray($order);
        }
    }

    /**
     * Add to orderDataArray Products info.
     * @param $order
     */
    private function _prepareOrderProductsArray(\Magento\Sales\Model\Order $order)
    {
        $this->orderDataArray["prod"]['label'] = __("Products");

        foreach ($order->getItems() as $item) {
            $this->orderDataArray["prod"]['value'][$item->getProductId()]['name'] = $item->getName();
            $this->orderDataArray["prod"]['value'][$item->getProductId()]['price_incl_tax'] = $this->pricingHelper->currency($item->getPriceInclTax(), true, false);
            $this->orderDataArray["prod"]['value'][$item->getProductId()]['qty_ordered'] = (int)$item->getQtyOrdered();
        }
    }

    /**
     * Add to orderDataArray Order Payment title.
     * @param $order
     */
    private function _prepareOrderPaymentArray(\Magento\Sales\Model\Order $order)
    {
        $paymentInstance = $this->paymentHelper->getMethodInstance($order->getPayment()->getMethod());

        $this->orderDataArray['payment_title'] = [
            'label' => __("Payment Title"),
            'value' => $paymentInstance->getTitle()
        ];
    }

    /**
     * Add to orderDataArray Order status info.
     * @param $order
     */
    private function _prepareOrderStatusArray(\Magento\Sales\Model\Order $order)
    {
        $this->orderDataArray['status'] = [
            'label' => __("Order Status"),
            'value' => $order->getStatus()
        ];
    }
}
?>