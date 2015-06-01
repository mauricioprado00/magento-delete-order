<?php

require_once 'abstract.php';

/**
 * @see http://stackoverflow.com/questions/4526914/how-can-i-delete-test-order-from-magento
 */
class Mage_Shell_DeleteOrder extends Mage_Shell_Abstract
{

    protected $_dryRun = false;

    /**
     * Run script
     *
     */
    public function run()
    {

        $this->_dryRun = (boolean) $this->getArg('dry-run');

        $order = $this->_getOrder();

        if ($order === null) {
            echo "the order does not exists\n";
        } elseif ($order === false) {
            echo $this->usageHelp();
        } else {
            if ($this->_dryRun) {
                echo "Dry Run\n\n";
            }

            $this->_printOrderInfo($order);

            if (!$this->_dryRun) {
                if (!$this->_deleteOrder($order)) {
                    echo "could not delete order\n";
                } else {
                    echo "order deleted\n";
                }
            }
        }

        echo PHP_EOL . PHP_EOL;
    }

    /**
     * print information of the order
     * @param Mage_Sales_Model_Order
     */
    private function _printOrderInfo($order)
    {
        $date = date('Ymd His', $order->getCreatedAt());
        echo <<< info
    Increment Id: {$order->getIncrementId()}
    Order Id: {$order->getId()}
    Status: {$order->getStatus()}
    Customer Id: {$order->getCustomerId()}
    Customer Email: {$order->getCustomerEmail()}
    Customer Firstname: {$order->getCustomerFirstname()}
    Customer Lastname: {$order->getCustomerLastname()}
    Date: {$date}

info;
    }

    /**
     * Deletes an order
     * @param Mage_Sales_Model_Order
     */
    private function _deleteOrder($order)
    {
        try {

            $order->delete();

        } catch(Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * it will return the order specified by the commandline parameters
     * if that exists, null if not
     * @return Mage_Sales_Model_Order
     */
    private function _getOrder()
    {
        $orderId = $this->getArg('order-id');
        $incrementId = $this->getArg('increment-id');

        if ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
        } elseif ($incrementId) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        } else {
            return false;
        }

        return $order && $order->getId() ? $order : null;
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f delete-order.php -- [options]

    --dry-run           It wont delete the order, it will just show you the info of the order
    --increment-id      It will delete the order with the provided increment id
    --order-id          It will delete the order with the provided order id

example:
    #delete order by increment id
    php -f shell/delete-order.php -- --increment-id 100000001

    #test delete order by increment id (wont actually delete it)
    php -f shell/delete-order.php -- --dry-run --order-id 32

USAGE;
    }
}

$shell = new Mage_Shell_DeleteOrder();
$shell->run();
