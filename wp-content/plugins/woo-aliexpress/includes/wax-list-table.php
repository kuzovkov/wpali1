<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class wax_wc_item_list_table extends WP_List_Table {
	
	private $appKey, $track;
	
	/** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
		
		//Set parent defaults
        parent::__construct( array(
            'singular'  => 'product',     //singular name of the listed records
            'plural'    => 'products',    //plural name of the listed records
            'ajax'      => true        //does this table support ajax?
        ) );
        
    }
	
	// Check AppKey & Tracking ID in plugin setting page
	function wax_check_setting_field(){
		
		/**
		 * Check if WooCommerce is active
		 **/
		if(class_exists('woocommerce')){
			$this->appKey = get_option('wax_appKey');
			$this->track = get_option('wax_track_id');
			if(empty($this->appKey) || empty($this->track)){
				$msg = 'Please enter AppKey and Tracking ID in plugin setting page!';
				add_settings_error('wax_active_woo', esc_attr( 'settings_updated' ), $msg, 'error');
				settings_errors('wax_active_woo');
			}
		}else{
			$msg = 'Please Install or Active WooCommerce!';
			add_settings_error('wax_active_woo', esc_attr( 'settings_updated' ), $msg, 'error');
			settings_errors('wax_active_woo');
		}
	}
	
	/** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
			case 'imageUrl':
				$cats = (isset($_GET['product_categories'])) ? implode(',', $_GET['product_categories']) : '' ;
                return sprintf('<a class="importer" data-id="%1$s" data-name="%2$s" data-url="%3$s" data-originalPrice="%4$s" data-salePrice="%5$s" data-cat="%6$s" data-cats="%7$s" data-tag="%8$s" href="%9$s"><img src="%10$s" title="%11$s" width="100" height="100"></a>',
							$item['productId'],
							$item['productTitle'],
							$item['productUrl'],
							$item['originalPrice'],
							$item['salePrice'],
							$_GET['categorie_id'],
							$cats,
							$_GET['product_tags'],
							$item['productUrl'],
							$item[$column_name] . '_100x100.jpg',
							$item['productTitle']
						);
			case 'commission':
                return 'US $' . (str_replace('%', '', 8) / 100) * str_replace(array('US $', '.00'), '', $item['salePrice']);
			case 'commissionRate':
                return '8.00% Fix!';
            default:
                return $item[$column_name];
        }
    }

	/** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (product title only)
     **************************************************************************/
    function column_cb($item){
		$cats = (isset($_GET['product_categories'])) ? implode(',', $_GET['product_categories']) : '' ;
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("product")
            /*$2%s*/  $item['productId']
			  . '@' . htmlentities($item['productTitle'])
			  . '@' . htmlentities($item['productUrl'])
			  . '@' . htmlentities($item['originalPrice'])
			  . '@' . htmlentities($item['salePrice'])
			  . '@' . htmlentities($_GET['categorie_id'])     //The value of the checkbox should be the record's id
			  . '@' . htmlentities($cats)
			  . '@' . htmlentities($_GET['product_tags'])
        );
    }

	/** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'imageUrl' => 'Thumbnail',
			'productTitle' => 'Title',
			'originalPrice' => 'Original Price',
			'salePrice' => 'Sale Price',
			'commissionRate' => 'Commission Rate',
			'commission' => 'Commission Expense',
			'volume' => 'Volume',
			'discount' => 'Discount',
			'packageType' => 'Type',
			'lotNum' => 'lot Number',
			'evaluateScore' => 'Evaluated Score',
			'validTime' => 'Valid Date',
        );
        return $columns;
    }
	
	/** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
	function get_sortable_columns() {
        $sortable_columns = array(
            'originalPrice'     => array('originalPrice',false),
			'commissionRate'     => array('commissionRate',false),
			'volume'     => array('volume',false),
			'validTime'     => array('validTime',false),
        );
        return $sortable_columns;
    }
	
	function get_hidden_columns(){
		$hidden_columns =  (array) get_user_option( 'toplevel-page-wax-product-list' );
        return $hidden_columns;
    }

	/** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'import'    => 'Import products'
        );
        return $actions;
    }

	/** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        
        if( 'import' === $this->current_action() ) {
			$items = $_POST['product'];
			if( $items != ''){
				echo "<div class=\"wrap\"> <h2>Bulk product import</h2>";
				foreach($items as $item){
					$data = explode('@', html_entity_decode($item));
					$p_id = $data[0];
					$p_name = $data[1];
					$p_url = $data[2];
					$p_originalPrice = $data[3];
					$p_salePrice = $data[4];
					$p_cat = $data[5];
					$p_cats = $data[6];
					$p_tags = $data[7];
					$product_id = wax_wc_insert_product($p_id, $p_name, $p_url, $p_originalPrice, $p_salePrice, $p_cat, $p_cats, $p_tags);
					
					if( strpos($product_id, 'Product inserted') !== false ){
						$msg = "$p_name <code>Added Successfully</code>";
						add_settings_error('wax_bulk_add', esc_attr( 'settings_updated' ), $msg, 'updated');
					}elseif( $product_id == 'This product already exist!') {
						$msg = "This $p_name <code> already exist!</code>";
						add_settings_error('wax_bulk_add', esc_attr( 'settings_updated' ), $msg, 'error');
					}else {
						$msg = "$p_name <code>Send Error!</code>";
						add_settings_error('wax_bulk_add', esc_attr( 'settings_updated' ), $msg, 'error');
					}
					
				}
				settings_errors('wax_bulk_add');
				echo "</div>";
			}
            # wp_die();
        }
        
    }

	/** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries
		
		$this->wax_check_setting_field();
		
		/**
         * First, lets decide how many records per page to show
         */
        $per_page = get_option('wax_product_num');
		
		/**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
		
		/**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = $this->get_column_info();
 		
		/**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
		
		/**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = (isset($_GET['paged'])) ? $_GET['paged'] : 1;
		
		/**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example 
         * package slightly different than one you might build on your own. In 
         * this example, we'll be using array manipulation to sort and paginate 
         * our data. In a real-world implementation, you will probably want to 
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
		$data = array();
		$total_items = 0;
			
		if( get_option('wax_purchase_valid') != 'valid' ){
			$data = array();
			$msg = 'Please enter valid purchase code to active plugin!';
			add_settings_error('wax_product_list', esc_attr( 'settings_updated' ), $msg, 'error');
			settings_errors('wax_product_list');
			return false;
		}
		
		$keywords = (isset($_GET['search_text'])) ? str_replace(" ", "+", $_GET['search_text']) : '';
		$categoryId = (isset($_GET['categorie_id'])) ? $_GET['categorie_id'] : '';
			
		if( !empty($keywords) || $categoryId != 0 ){
			
			$commissionRateFrom = (isset($_GET['minRange']) && !empty($_GET['minRange'])) ? "&commissionRateFrom={$_GET['minRange']}" : '';
			$commissionRateTo = (isset($_GET['maxRange']) && !empty($_GET['maxRange'])) ? "&commissionRateTo={$_GET['maxRange']}" : '';
			$originalPriceFrom = (isset($_GET['minPrice']) && !empty($_GET['minPrice'])) ? "&originalPriceFrom={$_GET['minPrice']}" : '';
			$originalPriceTo = (isset($_GET['maxPrice']) && !empty($_GET['maxPrice'])) ? "&originalPriceTo={$_GET['maxPrice']}" : '';
			
			$startCredit = (isset($_GET['startCredit']) && !empty($_GET['startCredit'])) ? "&startCreditScore={$_GET['startCredit']}" : '';
			$endCredit = (isset($_GET['endCredit']) && !empty($_GET['endCredit'])) ? "&endCreditScore={$_GET['endCredit']}" : '';
			
			$request_sort = '';
			
			if( isset($_GET['orderby']) ){
				$request_sort = '&sort=';
				switch( $_GET['orderby'] ){
					case 'originalPrice':
						if( $_GET['order'] == 'asc' ){
							$request_sort .= "orignalPriceDown";
						}elseif( $_GET['order'] == 'desc' ){
							$request_sort .= "orignalPriceUp";
						}
						
						break;
					case 'commissionRate':
						if( $_GET['order'] == 'asc' ){
							$request_sort .= "commissionRateDown";
						}elseif( $_GET['order'] == 'desc' ){
							$request_sort .= "commissionRateUp";
						}
						
						break;
					case 'volume':
						if( $_GET['order'] == 'asc' ){
							$request_sort .= "volumeDown";
						}elseif( $_GET['order'] == 'desc' ){
							$request_sort .= "";
						}
						
						break;
					case 'validTime':
						if( $_GET['order'] == 'asc' ){
							$request_sort .= "validTimeDown";
						}elseif( $_GET['order'] == 'desc' ){
							$request_sort .= "validTimeUp";
						}
						
						break;
					default:
							$request_sort = '';
				}
			}
			
		
			/*
			$request_url = "http://gw.api.alibaba.com/openapi/param2/1/portals.open/";
			$request_api = "api.listPromotionProduct/{$this->appKey}?trackingId={$this->track}";
			$request_param = "&categoryId={$categoryId}&pageNo={$current_page}&keywords={$keywords}&pageSize=10";
			$request_detail = "&commissionRateFrom={$minRange}&commissionRateTo={$maxRange}&priceFrom={$minPrice}&priceTo={$maxPrice}";
			*/
			
			$request_url = "http://gw.api.alibaba.com/openapi/param2/2/portals.open/";
			$request_api = "api.listPromotionProduct/{$this->appKey}?fields=";
			$request_fields = "totalResults,productId,productTitle,productUrl,imageUrl,originalPrice,salePrice,discount,evaluateScore,commission,commissionRate,30daysCommission,volume,packageType,lotNum,validTime";
			$request_param = "&categoryId={$categoryId}&pageNo={$current_page}&keywords={$keywords}&pageSize={$per_page}";
			$request_detail = $commissionRateFrom . $commissionRateTo . $originalPriceFrom . $originalPriceTo . $startCredit . $endCredit;
			
			$request = wp_remote_get($request_url . $request_api. $request_fields . $request_param . $request_detail . $request_sort);
			
			$errorCode = '';
			
			if ( is_wp_error($request) ){
				$msg = 'alibaba.com not response!';
				add_settings_error('wax_product_list', esc_attr( 'settings_updated' ), $msg, 'error');
			}else{
				$items = json_decode($request['body'], true);
				$errorCode =  (isset($items['errorCode'])) ? $items['errorCode'] : '';
			}
			
			if( $errorCode == 20010000 && !empty($items['result']['products']) ){
				$data = $items['result']['products'];
				$total_items = $items['result']['totalResults'];
			}if( $errorCode == 20010000 && empty($items['result']['products']) ){
				$msg = 'There is no product to display!';
				add_settings_error('wax_product_list', esc_attr( 'settings_updated' ), $msg, 'error');
			}
			elseif( isset($items['error_code']) == 400 ){
				$msg = $items['error_message'];
				add_settings_error('wax_product_list', esc_attr( 'settings_updated' ), $msg, 'error');
			}
			elseif( $errorCode == 20030000 ){
				$msg = 'Required parameters';
				add_settings_error('wax_product_list', esc_attr( 'settings_updated' ), $msg, 'error');
			}
			elseif( $errorCode == 20030010 ){
				$msg = 'Keyword input parameter error';
				add_settings_error('wax_product_list', esc_attr( 'settings_updated' ), $msg, 'error');
			}
			elseif( $errorCode == 20030020 ){
				$msg = 'Category ID input parameter error or formatting errors';
				add_settings_error('wax_product_list', esc_attr( 'settings_updated' ), $msg, 'error');
			}
			elseif( $errorCode == 20030030 ){
				$msg = 'Commission rate input parameter error or formatting errors';
				add_settings_error('wax_product_list', esc_attr( 'settings_updated' ), $msg, 'error');
			}
			elseif( $errorCode == 20030040 ){
				$msg = 'Unit input parameter error or formatting errors';
				add_settings_error('wax_product_list', esc_attr( 'settings_updated' ), $msg, 'error');
			}
			elseif( $errorCode == 20030050 ){
				$msg = '30 days promotion amount input parameter error or formatting errors';
				add_settings_error('wax_product_list', esc_attr( 'settings_updated' ), $msg, 'error');
			}
			elseif( $errorCode == 20030060 ){
				$msg = 'Tracking ID input parameter error or limited length';
				add_settings_error('wax_product_list', esc_attr( 'settings_updated' ), $msg, 'error');
			}
			elseif( $errorCode == 20030070 ){
				$msg = 'Unauthorized transfer request';
				add_settings_error('wax_product_list', esc_attr( 'settings_updated' ), $msg, 'error');
			}
			elseif( $errorCode == 20020000 ){
				$msg = 'System Error';
				add_settings_error('wax_product_list', esc_attr( 'settings_updated' ), $msg, 'error');
			}
		}else{
			$msg = 'Please enter some search text or select item from category list!';
			add_settings_error('wax_product_list', esc_attr( 'settings_updated' ), $msg, 'updated');
		}
		
		settings_errors('wax_product_list');
		
		/**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
		
		/**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => floor($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
}

