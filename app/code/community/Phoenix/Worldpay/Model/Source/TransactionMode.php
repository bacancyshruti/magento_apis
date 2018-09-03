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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Phoenix
 * @package    Phoenix_Worldpay
 * @copyright  Copyright (c) 2008-2017 Phoenix Media GmbH & Co. KG (http://www.phoenix-media.eu)
 */

class Phoenix_Worldpay_Model_Source_TransactionMode
{
    public function toOptionArray()
    {
        $options =  array();       ;
        foreach (Mage::getSingleton('worldpay/config')->getTransactionModes() as $code => $name) {
            $options[] = array(
            	   'value' => $code,
            	   'label' => $name
            );
        }

        return $options;
    }
}