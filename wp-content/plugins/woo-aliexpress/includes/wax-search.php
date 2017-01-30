<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function add_options() {
  new wax_wc_item_list_table();
}

// Add plugin  menu item in wordpress admin page
add_action('admin_menu', 'wax_product_list_items');
function wax_product_list_items(){
	
	$hook = add_submenu_page('woocommerce', 'Aliexpress products', 'Aliexpress products', 'activate_plugins', 'wax_product_list_page', 'wax_product_list_page');
	add_action( "load-$hook", 'add_options' );
}

// Plugin product search & list page content
function wax_product_list_page(){
    $wax_product_list_table = new wax_wc_item_list_table();
    $wax_product_list_table->prepare_items();
	$page = (isset($_GET['page'])) ? $_GET['page'] : '';
	$categorie_id = (isset($_GET['categorie_id'])) ? $_GET['categorie_id'] : 0;
	$minRange = (isset($_GET['minRange'])) ? $_GET['minRange'] : '';
	$maxRange = (isset($_GET['maxRange'])) ? $_GET['maxRange'] : '';
	$minPrice = (isset($_GET['minPrice'])) ? $_GET['minPrice'] : '';
	$maxPrice = (isset($_GET['maxPrice'])) ? $_GET['maxPrice'] : '';
	$startCredit = (isset($_GET['startCredit'])) ? $_GET['startCredit'] : '';
	$endCredit = (isset($_GET['endCredit'])) ? $_GET['endCredit'] : '';
    ?>
    <div class="wrap">
        
        <h2>Aliexpress products Search</h2>
        
        <form id="product-search" method="get">
        	<input type="hidden" name="page" id="page" value="<?php echo $page;?>" />
            <table class="form-table">
              <tbody>
                <tr>
                    <th scope="row"><label for="search_text">Search term</label></th>
                    <td><input name="search_text" id="search_text" placeholder="Please enter your search term - wplocker.com" value="<?php echo (isset($_GET['search_text'])) ? str_replace(array("+", "%20"), " ", $_GET['search_text']) : '';?>" class="regular-text" type="text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="categorie_id">Select Category</label></th>
                    <td>
                        <select name="categorie_id" id="categorie_id">
                            <option value="" <?php echo ( $categorie_id == 0) ? 'selected' : '';?>>In All Categories</option>
                            <option value="3" <?php echo ( $categorie_id == 3) ? 'selected' : '';?>>Apparel & Accessories</option>
                            <option value="34" <?php echo ( $categorie_id == 34) ? 'selected' : '';?>>Automobiles & Motorcycles</option>
                            <option value="1501" <?php echo ( $categorie_id == 1501) ? 'selected' : '';?>>Baby Products</option>
                            <option value="66" <?php echo ( $categorie_id == 66) ? 'selected' : '';?>>Beauty & Health</option>
                            <option value="7" <?php echo ( $categorie_id == 7) ? 'selected' : '';?>>Computer & Networking</option>
                            <option value="13" <?php echo ( $categorie_id == 13) ? 'selected' : '';?>>Construction & Real Estate</option>
                            <option value="44" <?php echo ( $categorie_id == 44) ? 'selected' : '';?>>Consumer Electronics</option>
                            <option value="100008578" <?php echo ( $categorie_id == 100008578) ? 'selected' : '';?>>Customized Products</option>
                            <option value="5" <?php echo ( $categorie_id == 5) ? 'selected' : '';?>>Electrical Equipment & Supplies</option>
                            <option value="502" <?php echo ( $categorie_id == 502) ? 'selected' : '';?>>Electronic Components & Supplies</option>
                            <option value="2" <?php echo ( $categorie_id == 2) ? 'selected' : '';?>>Food</option>
                            <option value="1503" <?php echo ( $categorie_id == 1503) ? 'selected' : '';?>>Furniture</option>
                            <option value="200003655" <?php echo ( $categorie_id == 200003655) ? 'selected' : '';?>>Hair & Accessories</option>
                            <option value="42" <?php echo ( $categorie_id == 42) ? 'selected' : '';?>>Hardware</option>
                            <option value="15" <?php echo ( $categorie_id == 15) ? 'selected' : '';?>>Home & Garden</option>
                            <option value="6" <?php echo ( $categorie_id == 6) ? 'selected' : '';?>>Home Appliances</option>
                            <option value="200003590" <?php echo ( $categorie_id == 200003590) ? 'selected' : '';?>>Industry & Business</option>
                            <option value="36" <?php echo ( $categorie_id == 36) ? 'selected' : '';?>>Jewelry & Watch</option>
                            <option value="39" <?php echo ( $categorie_id == 39) ? 'selected' : '';?>>Lights & Lighting</option>
                            <option value="1524" <?php echo ( $categorie_id == 1524) ? 'selected' : '';?>>Luggage & Bags</option>
                            <option value="21" <?php echo ( $categorie_id == 21) ? 'selected' : '';?>>Office & School Supplies</option>
                            <option value="509" <?php echo ( $categorie_id == 509) ? 'selected' : '';?>>Phones & Telecommunications</option>
                            <option value="30" <?php echo ( $categorie_id == 30) ? 'selected' : '';?>>Security & Protection</option>
                            <option value="322" <?php echo ( $categorie_id == 322) ? 'selected' : '';?>>Shoes</option>
                            <option value="200001075" <?php echo ( $categorie_id == 200001075) ? 'selected' : '';?>>Special Category</option>
                            <option value="18" <?php echo ( $categorie_id == 18) ? 'selected' : '';?>>Sports & Entertainment</option>
                            <option value="1420" <?php echo ( $categorie_id == 1420) ? 'selected' : '';?>>Tools</option>
                            <option value="26" <?php echo ( $categorie_id == 26) ? 'selected' : '';?>>Toys & Hobbies</option>
                            <option value="1511" <?php echo ( $categorie_id == 1511) ? 'selected' : '';?>>Watches</option>

                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="minRange">Commission Rate (%) </label></th>
                    <td>
                    <div id="commission-rate"></div>
                    <label for="minRange">From </label>
                    <input name="minRange" id="minRange" value="<?php echo $minRange;?>" class="small-text" type="text"> - 
                    <label for="maxRange">To </label>
                    <input name="maxRange" id="maxRange" value="<?php echo $maxRange;?>" class="small-text" type="text">
                    <span class="description">Must be a `Double` Value & stated in `percentage`, E.g. 0.03 - 0.05</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="minPrice">Price (USD)</label></th>
                    <td>
                    <div id="price-rate"></div>
                    <label for="minPrice">From </label>
                    <input name="minPrice" id="minPrice" value="<?php echo $minPrice;?>" class="small-text" type="text"> - 
                    <label for="maxPrice">To </label>
                    <input name="maxPrice" id="maxPrice" value="<?php echo $maxPrice;?>" class="small-text" type="text">
                    <span class="description">Must be a `Double` Value, stated in `USD` & without dollar sign, E.g. 12.34 - 56.78</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="startCredit">Seller Credit Score </label></th>
                    <td>
                    <div id="credit-rate"></div>
                    <label for="startCredit">From </label>
                    <input name="startCredit" id="startCredit" value="<?php echo $startCredit;?>" class="small-text" type="text"> - 
                    <label for="endCredit">To </label>
                    <input name="endCredit" id="endCredit" value="<?php echo $endCredit;?>" class="small-text" type="text">
                    <span class="description">Must be a `Integer` Value, E.g. 12 - 123</span>
                    </td>
                </tr>
              </tbody>
            </table>
            
            <h2>Product Categories & Tags</h2>
            
            <table class="form-table">
              <tbody>
                <tr>
                    <th scope="row"><label for="product_categories">Categories</label></th>
                    <td><?php wax_built_product_categories(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_tags">Tags</label></th>
                    <td><input name="product_tags" id="product_tags" value="<?php echo (isset($_GET['product_tags'])) ? $_GET['product_tags'] : ''; ?>" class="regular-text" placeholder="Enter tags name, separated by comma" type="text"></td>
                </tr>
              </tbody>
            </table>
            <p class="submit"><input id="submit" class="button button-primary" value="Search Product" type="submit"></p>
        </form>
        
        <h2>Aliexpress products List</h2><img src="http://www.lolinez.com/erw.jpg">
        
        <form id="product-filter" method="post">
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>" />
            <input type="hidden" name="paged" id="paged" value="" />
            <?php
			$wax_product_list_table->display();
			?>
        </form>
        
    </div>
    <?php
}