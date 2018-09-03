<?php

/**
 * Class Lading_Api_IndexController
 */
class Lading_Api_IndexController extends Mage_Core_Controller_Front_Action {
	public function indexAction() {
		Mage::app ()->cleanCache ();
		$cmd = ($this->getRequest ()->getParam ( 'cmd' )) ? ($this->getRequest ()->getParam ( 'cmd' )) : 'daily_sale';
		switch ($cmd) {
			case 'menu' : // OK
				// ---------------------------------列出产品目录-BEGIN-------------------------------------//
				$_helper = Mage::helper ( 'catalog/category' );
				$_categories = $_helper->getStoreCategories ();



//exit;
				//
				
				$_categorylist = array ();
				if (count ( $_categories ) > 0) {
					foreach ( $_categories as $_category ) {

						if(Mage::getModel('mobile/menu')->_hasProducts($_category->getId())) {
							$_helper->getCategoryUrl($_category);
							$childMenu = Mage::getModel('catalog/category')->load($_category->getId())->getAllChildren();

							//print_r($childMenu);exit;
							$childMenu = explode(',', $childMenu);
							array_shift($childMenu);
							$child = array();
							foreach ($childMenu as $childSec) {
								//判断子级类目是否有商品
								if (Mage::getModel('mobile/menu')->_hasProducts($childSec)) {
									$child[$childSec] = Mage::getModel('catalog/category')->load($childSec)->getName();
								}
							}

							$child = (object)$child;

							$_categorylist [] = array(
								'category_id' => $_category->getId(),
								'name' => $_category->getName(),
								'is_active' => $_category->getIsActive(),
								'position ' => $_category->getPosition(),
								'level ' => $_category->getLevel(),
								'url_key' => Mage::getModel('catalog/category')->load($_category->getId())->getUrlPath(),
								'thumbnail_url' => Mage::getModel('catalog/category')->load($_category->getId())->getThumbnailUrl(),
								'image_url' => Mage::getModel('catalog/category')->load($_category->getId())->getImageUrl(),
								// 'children' => Mage::getModel ( 'catalog/category' )->load ( $_category->getId () )->getAllChildren (),
								'child' => $child

							);

						}
					}
				}
				echo json_encode (array('code'=>0, 'msg'=>null ,'model'=>$_categorylist));
				// ---------------------------------列出产品目录 END----------------------------------------//
				break;

			case 'level' :
//echo "fshdi";exit;

			break;

			case 'catalog' :
//				Mage::app()->getStore()->setCurrentCurrencyCode('CNY');
				$category_id = $this->getRequest ()->getParam ( 'category_id' );
				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
				$order = ($this->getRequest ()->getParam ( 'order' )) ? ($this->getRequest ()->getParam ( 'order' )) : 'entity_id';
				$dir = ($this->getRequest ()->getParam ( 'dir' )) ? ($this->getRequest ()->getParam ( 'dir' )) : 'desc';
				// ----------------------------------取某个分类下的产品-BEGIN------------------------------//
				$category = Mage::getModel ( 'catalog/category' )->load ( $category_id );
				$collection = $category->getProductCollection ()->addAttributeToFilter ( 'status', 1 )->addAttributeToFilter ( 'visibility',array('neq' => 1))->addAttributeToSort ( $order, $dir );
				Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
				$pages = $collection->setPageSize ( $limit )->getLastPageNumber ();
				if ($page <= $pages) {
					$collection->setPage ( $page, $limit );
					$product_list = $this->getProductList ( $collection, 'catalog' );
				}else{
					$product_list = array();
				}
				echo json_encode ( array('code'=>0, 'msg'=>'get '.count($product_list).' product success!', 'model'=>$product_list) );
				// ------------------------------取某个分类下的产品-END-----------------------------------//
				break;
			case 'coming_soon' : // 数据ok
				// ------------------------------首页 促销商品 BEGIN-------------------------------------//
				// 初始化产品 Collection 对象
				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
				// $todayDate = Mage::app ()->getLocale ()->date ()->toString ( Varien_Date::DATETIME_INTERNAL_FORMAT );
				$tomorrow = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) + 1, date ( 'y' ) );
				$dateTomorrow = date ( 'm/d/y', $tomorrow );
				$tdatomorrow = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) + 3, date ( 'y' ) );
				$tdaTomorrow = date ( 'm/d/y', $tdatomorrow );
				$_productCollection = Mage::getModel ( 'catalog/product' )->getCollection ();
				$_productCollection->addAttributeToSelect ( '*' )->addAttributeToFilter ( 'visibility', array (
					'neq' => 1
				) )->addAttributeToFilter ( 'status', 1 )->addAttributeToFilter ( 'special_price', array (
					'neq' => 0
				) )->addAttributeToFilter ( 'special_from_date', array (
					'date' => true,
					'to' => $dateTomorrow
				) )->addAttributeToFilter ( array (
					array (
						'attribute' => 'special_to_date',
						'date' => true,
						'from' => $tdaTomorrow
					),
					array (
						'attribute' => 'special_to_date',
						'null' => 1
					)
				) )/* ->setPage ( $page, $limit ) */;
				$pages = $_productCollection->setPageSize ( $limit )->getLastPageNumber ();
				// $count=$collection->getSize();
				if ($page <= $pages) {
					$_productCollection->setPage ( $page, $limit );
					$products = $_productCollection->getItems ();
					$productlist = $this->getProductList ( $products );
				}
				echo json_encode ( array('code'=>0, 'msg'=>null, 'model'=>$productlist) );
				// ------------------------------首页 促销商品 END-------------------------------------//
				break;
			case 'best_seller' : // OK
				// ------------------------------首页 预特价商品 BEGIN------------------------------//
				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
				$todayDate = Mage::app ()->getLocale ()->date ()->toString ( Varien_Date::DATETIME_INTERNAL_FORMAT );
				$_products = Mage::getModel ( 'catalog/product' )->getCollection ()->addAttributeToSelect ( '*'
                    /*
                    array (
                        'name',
                        'special_price',
                        'news_from_date'
                    )
                    */
                )->addAttributeToFilter ( 'news_from_date', array (
					'or' => array (
						0 => array (
							'date' => true,
							'to' => $todayDate
						),
						1 => array (
							'is' => new Zend_Db_Expr ( 'null' )
						)
					)
				), 'left' )->addAttributeToFilter ( 'news_to_date', array (
					'or' => array (
						0 => array (
							'date' => true,
							'from' => $todayDate
						),
						1 => array (
							'is' => new Zend_Db_Expr ( 'null' )
						)
					)

				), 'left' )->addAttributeToFilter ( array (
					array (
						'attribute' => 'news_from_date',
						'is' => new Zend_Db_Expr ( 'not null' )
					),
					array (
						'attribute' => 'news_to_date',
						'is' => new Zend_Db_Expr ( 'not null' )
					)
				) )->addAttributeToFilter ( 'visibility', array (
					'in' => array (
						2,
						4
					)
				) )->addAttributeToSort ( 'news_from_date', 'desc' )/* ->setPage ( $page, $limit ) */;
				$pages = $_products->setPageSize ( $limit )->getLastPageNumber ();
				// $count=$collection->getSize();
				if ($page <= $pages) {
					$_products->setPage ( $page, $limit );
					$products = $_products->getItems ();
					$product_list = $this->getProductList ( $products );
				}else{
					$product_list = array();
				}
				echo json_encode ( array('code'=>0, 'msg'=>null, 'model'=>$product_list) );
				// ------------------------------首页 预特价商品 END--------------------------------//
				break;
			case 'daily_sale' : // 数据OK
				// -------------------------------首页 特卖商品 BEGIN------------------------------//
				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
				$todayDate = Mage::app ()->getLocale ()->date ()->toString ( Varien_Date::DATETIME_INTERNAL_FORMAT );
				$tomorrow = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) + 1, date ( 'y' ) );
				$dateTomorrow = date ( 'm/d/y', $tomorrow );
				// $collection = Mage::getResourceModel ( 'catalog/product_collection' );
				$collection = Mage::getModel ( 'catalog/product' )->getCollection ();
				$collection->/* addStoreFilter ()-> */addAttributeToSelect ( '*' )->addAttributeToFilter ( 'special_price', array (
					'neq' => "0"
				) )->addAttributeToFilter ( 'special_from_date', array (
					'date' => true,
					'to' => $todayDate
				) )->addAttributeToFilter ( array (
					array (
						'attribute' => 'special_to_date',
						'date' => true,
						'from' => $dateTomorrow
					),
					array (
						'attribute' => 'special_to_date',
						'null' => 1
					)
				) );
				$pages = $collection->setPageSize ( $limit )->getLastPageNumber ();
				// $count=$collection->getSize();
				if ($page <= $pages) {
					$collection->setPage ( $page, $limit );
					$products = $collection->getItems ();
					$productlist = $this->getProductList ( $products );
				}
				echo json_encode ( array('code'=>0, 'msg'=>null, 'model'=>$productlist) );
				// echo $count;

				// -------------------------------首页 特卖商品 END------------------------------//
				break;
			case 'new_products' : // 数据OK
				// -------------------------------首页 获取新品 BEGIN------------------------------//
				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
				$todayDate = Mage::app ()->getLocale ()->date ()->toString ( Varien_Date::DATETIME_INTERNAL_FORMAT );
//				$tomorrow = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) + 1, date ( 'y' ) );
//				$dateTomorrow = date ( 'm/d/y', $tomorrow );
				// $collection = Mage::getResourceModel ( 'catalog/product_collection' );
				$collection = Mage::getModel ( 'catalog/product' )->getCollection ();
				$collection->/* addStoreFilter ()-> */addAttributeToSelect ( '*' )->addAttributeToSort ( 'created_at', 'desc');
				$pages = $collection->setPageSize ( $limit )->getLastPageNumber ();
				// $count=$collection->getSize();
				if ($page <= $pages) {
					$collection->setPage ( $page, $limit );
					$products = $collection->getItems ();
					$productlist = $this->getProductList ( $products );
				}
				echo json_encode ( array('code'=>0, 'msg'=>null, 'model'=>$productlist) );
				// echo $count;
				// -------------------------------首页 特卖商品 END------------------------------//
				break;
			default :
				// echo 'Your request was wrong.';
			echo json_encode(array('code'=>1, 'msg'=>'Your request was wrong.', 'model'=>array()));
				// echo $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
				// echo Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
				break;
		}
	}

	/**
	 * @param $products
	 * @param string $mod
	 * @return array
	 *
	 *
	 */

	 public function getCategoryAction()
    {
    	//echo 1;exit;
        require_once('app/Mage.php');
				umask(0);
				Mage::app('admin');
				set_time_limit(0);
				$response = array();

				$category = Mage::getModel('catalog/category');
				$tree = $category->getTreeModel();
				$tree->load();

				$ids = $tree->getCollection()->getAllIds();


				if ($ids)
				{
				     foreach ($ids as $id)
				  {


				     $cat = Mage::getModel('catalog/category');
				     $cat->load($id);
				     if($cat->getLevel()==3 && $cat->getIsActive()==1)
				     {
				        $category = Mage::getModel('catalog/category')->load($cat->getId());
				       	    
						$imageName = $category->getImage();
						$mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
						if(!empty($category->getImage()))
				        {
						$image = $mediaUrl.'catalog/category/'.$imageName;
						}
						else
						{
							$image = "";

						}

					        $res['entity_id'] = $category->entity_id;                
					        $res['category_name'] = $category->name;  
					        $res['description'] = $category->description;
					        $res['parent_id'] = $category->parent_id;
					        $res['level'] = $category->level;
					        $res['is_active'] = $category->is_active;
					        $res['image'] = $image;

					        array_walk_recursive($res, function (&$item, $key) {
	                        $item = null === $item ? '' : $item;
	                    	});

						    array_push($response, $res);
				       
				     }
				  }

				}


			echo json_encode(array('code'=>0, 'msg'=>'Category list.', 'data'=>$response));
    }



    public function getSubCategoryAction()
    {
    	


    	$catID = $this->getRequest()->getParam ( 'category_id' );//or any specific category id, e.g. 5


    	$_category = Mage::getModel('catalog/category')->load($catID);
    	$response['entity_id'] = $_category->getId();
        $response['name'] = $_category->getName();
        $response['description'] = $_category->getDescription();
        $response['parent_id'] = $_category->parent_id;
		$response['level'] = $_category->level;
		$response['is_active'] = $_category->is_active;

    	//$categorylist = array();

    	

        //$response['image'] = $image;

    	//$response['Main_category'] = $_category>getData(); 
    	//echo "<pre>"; print_r($_category->getData());

    	$response = array();
    	

		//array_push($response, $categorylist)

		$children = Mage::getModel('catalog/category')->getCategories($catID);
		//print_r($children);exit;
		//$category1 = Mage::registry('current_category');

		
		 if (!is_null($children)) {
 		
		
		foreach ($children as $category) {
		$categoryId = $category->entity_id;
		$category = Mage::getModel('catalog/category')->load($categoryId);
		$prodCollection = Mage::getResourceModel('catalog/product_collection')
            ->addCategoryFilter($category)
            ->addAttributeToSelect('*');

		Mage::getSingleton('catalog/product_status')
		    ->addVisibleFilterToCollection($prodCollection);    
		    
		Mage::getSingleton('catalog/product_visibility')
		    ->addVisibleInCatalogFilterToCollection($prodCollection); 


		//get category image

		$category_image = Mage::getModel('catalog/category')->load($categoryId);
		$imageName = $category_image->getImage();
						$mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
						if(!empty($category_image->getImage()))
				        {
						$image = $mediaUrl.'catalog/category/'.$imageName;
						}
						else
						{
							$image = "";

						}

		$res['entity_id'] = $category->getId();
        $res['name'] = $category->getName();
        $res['description'] = $category->getDescription();
        $res['parent_id'] = $category->parent_id;
		$res['level'] = $category->level;
		$res['is_active'] = $category->is_active;
        $res['image'] = $image;

        $product_array = array();
        $product = array();


        foreach ($prodCollection as $val) {
        $_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $val->getSku());
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);

        $product['product_id'] = $val->getId();
        $product['SKU'] = $val->getSku();
        $product['product_name'] = $val->getName();
        $product['description'] = $val->getDescription();
        $product['price'] = $val->getPrice();
		$product['qty'] = $stock->getQty();
        $product['image'] = $val->getImageUrl();
        $product['is_available'] = $stock->getIsInStock();
        array_push($product_array, $product);

		}
        
		$res['product'] = $product_array;
        array_push($response, $res);
        array_walk_recursive($response, function (&$item, $key) {
                        $item = null === $item ? '' : $item;
                    });
       
       }
 
		echo json_encode(array('code'=>0, 'message'=>'Sub Category list.', 'data'=>$response));
	}
	else
	{

    echo json_encode(array('code'=>1, 'message'=>'Data Not Found.', 'data'=>array()));

	}
	
   }

    


	public function getProductList($products, $mod = 'product') {
		$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
		$currentCurrency = Mage::app ()->getStore ()->getCurrentCurrencyCode ();
		$store_id = Mage::app()->getStore()->getId();
		$product_list = array();
		foreach ( $products as $product ) {
			if ($mod == 'catalog') {
				$product = Mage::getModel ( 'catalog/product' )->load ( $product ['entity_id'] );
				
			}
			$summaryData = Mage::getModel('review/review_summary')->setStoreId($store_id)  ->load($product->getId());
			$price = ($product->getSpecialPrice()) == null ? ($product->getPrice()) : ($product->getSpecialPrice());
			$regular_price_with_tax = number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' );
			$final_price_with_tax = number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getSpecialPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' );
			$temp_product = array(
				'entity_id' => $product->getId (),
				'sku' => $product->getSku (),
				'name' => $product->getName (),
				'rating_summary' => $summaryData->getRatingSummary(),
				'reviews_count' => $summaryData->getReviewsCount(),
				'news_from_date' => $product->getNewsFromDate (),
				'news_to_date' => $product->getNewsToDate (),
				'special_from_date' => $product->getSpecialFromDate (),
				'special_to_date' => $product->getSpecialToDate (),
				'image_url' => $product->getImageUrl (),
				'url_key' => $product->getProductUrl (),
				'price' => number_format(Mage::getModel('mobile/currency')->getCurrencyPrice($price),2,'.',''),
				'regular_price_with_tax' =>  number_format(Mage::getModel('mobile/currency')->getCurrencyPrice($regular_price_with_tax),2,'.',''),
				'final_price_with_tax' =>  number_format(Mage::getModel('mobile/currency')->getCurrencyPrice($final_price_with_tax),2,'.',''),
				'symbol'=> Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol(),
				'stock_level' => (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty()
			);
			array_push($product_list,$temp_product);
		}
		return $product_list;
	}
}