<?php
class Lading_Api_WishlistController extends Mage_Core_Controller_Front_Action {

	/**
     * add item to user wish list
     */
	public function addAction() {
		$return_result = array(
			'code' => 0,
			'message' => null,
			'data' => null
		);
		if (! Mage::getStoreConfigFlag('wishlist/general/active')) {
			$return_result ['code'] = 1;
			$return_result ['message'] = 'Wishlist Has Been Disabled By Admin';
			echo json_encode($return_result);
			return;
		}
		if (! Mage::getSingleton('customer/session')->isLoggedIn()) {
			$return_result ['code'] = 5;
			$return_result ['message'] = 'Please Login First';
			echo json_encode($return_result);
			return;
		}
		$customer_id = Mage::getSingleton('customer/session')->getId();
		$customer = Mage::getModel('customer/customer');
		$wishlist = Mage::getModel('wishlist/wishlist');
		$product  = Mage::getModel('catalog/product');
		$product_id  = $_GET['product_id'];
		$customer->load($customer_id);
		$wishlist->loadByCustomer($customer_id);
		if($customer_id && $product_id){
			$res = $wishlist->addNewItem($product->load($product_id));
			if($res){
				$return_result['code'] = 0;
				$return_result['message'] = "your product has been added in wishlist";
				$return_result['data'] = $res;
			}
			echo json_encode($return_result);
		}else{
			$return_result['code'] = 1;
			$return_result['message'] = 'can not get customer info or product id';
			$return_result['data'] = null;
			echo json_encode($return_result);
		}
	}



    /**
     * get user wish list action
     */
	public function getWishlistAction(){
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			echo json_encode(
				array(
					'code' => 0,
					'message' => 'get user wish list success!',
					'data' => $this->_getWishlist()
				)
			);
		}else{
			echo json_encode(array(
				'code' => 5,
				'message' => 'You already logged into another device. You have to logout First.',
				'data'=>array ()
			));
		}
	}




	/**
	 * delete wish list action
	 */
	public function delAction(){
		$product_id  = $_GET['product_id'];
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customer_id =  Mage::getSingleton ( 'customer/session' )->getCustomer ()->getId();
			$item_collection = Mage::getModel('wishlist/item')->getCollection()->addCustomerIdFilter($customer_id);
			foreach($item_collection as $item) {
				if($item->getProductId()==$product_id){
					$item->delete();
				}
			}
			echo json_encode(
				array(
					'code' => 0,
					'message' => 'Product has been removed from your Wishlist.',
					'data' => $this->_getWishlist()
				)
			);
		}else{
			echo json_encode(array(
				'code' => 5,
				'message' => 'You already logged into another device. You have to logout First.',
				'data'=>array ()
			));
		}
	}


    /**
     * get wish list method
     * @return array|bool
     */
	protected function _getWishlist() {
		$wishlist = Mage::registry ( 'wishlist' );
		$store_id = Mage::app()->getStore()->getId();
		$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
		$currentCurrency = Mage::app ()->getStore ()->getCurrentCurrencyCode ();
		if ($wishlist) {
			return $wishlist;
		}
		try {
			$wishlist = Mage::getModel ( 'wishlist/wishlist' )->loadByCustomer ( Mage::getSingleton ( 'customer/session' )->getCustomer (), true );
			Mage::register ( 'wishlist', $wishlist );
		} catch ( Mage_Core_Exception $e ) {
			Mage::getSingleton ( 'wishlist/session' )->addError ( $e->getMessage () );
		} catch ( Exception $e ) {
			Mage::getSingleton ( 'wishlist/session' )->addException ( $e, Mage::helper ( 'wishlist' )->__ ( 'Cannot create wishlist.' ) );
			return false;
		}
		$items = array ();
		foreach ( $wishlist->getItemCollection () as $item ) {
			$item = Mage::getModel ( 'catalog/product' )->setStoreId ( $item->getStoreId () )->load ( $item->getProductId () );
			$summaryData = Mage::getModel('review/review_summary')->setStoreId($store_id)  ->load($item->getId());
			if ($item->getId ()) {
				$price = Mage::getModel('mobile/currency')->getCurrencyPrice(($item->getSpecialPrice()) == null ? ($item->getPrice()) : ($item->getSpecialPrice()));
				$items [] = array (
					'name' => $item->getName (),
					'image_url' => $item->getImageUrl (),
					'url_key' => $item->getProductUrl (),
					'rating_summary' => $summaryData->getRatingSummary(),
					'reviews_count' => $summaryData->getReviewsCount(),
					'entity_id' => $item->getId (),
					'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $item->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
					'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $item->getSpecialPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
					'price' => number_format($price, 2, '.', '' ),
					'sku' => $item->getSku(),
					'symbol' => Mage::app()->getLocale()->currency ( Mage::app ()->getStore ()->getCurrentCurrencyCode () )->getSymbol (),
					'stock_level' => (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($item)->getQty(),
					'short_description' => $item->getShortDescription()
				);
			}
		}
		return array (
			'wishlist' => $wishlist->getData (),
			'items' => $items
		);
	}
} 
