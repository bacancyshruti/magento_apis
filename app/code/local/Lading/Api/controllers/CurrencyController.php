<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Directory
 * @copyright  Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Currency controller
 *
 * @category   Mage
 * @package    Mage_Directory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Lading_Api_CurrencyController extends Mage_Core_Controller_Front_Action
{
    /**
     * switch currency
     */
    public function switchAction(){
        if ($curency = (string) $this->getRequest()->getParam('currency')) {
            Mage::app()->getStore()->setCurrentCurrencyCode($curency);
        }
        echo json_encode(array(
            'code'=>0,
            'msg' => 'set current currency code success!',
            'model'=>null
        ));
    }

    /**
     * get current currency code
     */
    public function getAction(){
        $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
        $currency_symbol = Mage::app()->getLocale()->currency( $currency_code )->getSymbol();
        $currency_name = Mage::app()->getLocale()->currency( $currency_code )->getName();
        $result = array(
            'currency_code' => $currency_code,
            'currency_symbol' => $currency_symbol,
            'currency_name' => $currency_name
        );
        echo json_encode(array(
            'code'=>0,
            'msg' => 'set current currency code success!',
            'model'=>$result
        ));
    }

    public function get_worldpayAction(){

  //  echo 1;exit;

 define('MAGENTO_ROOT', getcwd());
$mageFilename = MAGENTO_ROOT . '/app/Mage.php';

require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app();
$dbtransaction = Mage::getModel('worldpaycw/transaction')->load
($transactionId);
print_r($dbtransaction);exit;
$transactionObject = $dbtransaction->getTransactionObject();
$dbtransaction = Mage::getModel('worldpaycw/transaction')->load
($orderId,'transaction_external_id');
$transactionObject = $dbtransaction->getTransactionObject();


}
}
