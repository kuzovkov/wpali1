<?php

class Wax_Woo_Aliexpress {

    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public function __construct() {
		$this->id    = 'wooaliexpress';
		
        add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
        add_action( 'woocommerce_settings_tabs_' . $this->id, array( $this, 'settings_tab' ) );
        add_action( 'woocommerce_update_options_' . $this->id, array( $this, 'update_settings' ) );
    }
    
    
    /**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['wooaliexpress'] = 'Woo Aliexpress';
        return $settings_tabs;
    }
	
	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			'' => 'Api Settings',
			'category-tag-settings' => 'Category & Tag Settings',
			'product-data-settings' => 'Product Data Settings',
			'import-settings' => 'Import Settings',
			'auto-update-settings' => 'Auto Update Settings',
		);

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Output sections
	 */
	public function output_sections() {
		global $current_section;
		
		$sections = $this->get_sections();

		if ( empty( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}


    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses $this->get_settings()
     */
    public function settings_tab() {
		global $current_section;
		
		$this->output_sections();
        woocommerce_admin_fields( $this->get_settings() );
    }


    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @uses $this->get_settings()
     */
    public function update_settings() {
        woocommerce_update_options( $this->get_settings() );
		
		if( isset($_POST['wax_update_schedule']) ){
			add_action( 'wp', 'wax_update_product_setup_schedule' );
			wax_update_product_setup_schedule();
		}
		
		if( isset($_POST['wax_currency_server']) ){
			wax_update_remote_currency_rate();
		}
			
		if( isset($_POST['wax_purchase_code']) ){
			$check_license = wax_getRemote_information('license', $_POST['wax_purchase_code']);
			if( isset($check_license->valid) && $check_license->valid ){
				update_option('wax_purchase_valid', 'valid');
				add_settings_error('wax_active', esc_attr( 'settings_updated' ), 'Thank you! Plugin is activated!', 'updated');
				settings_errors('wax_active');
			}else{
				update_option('wax_purchase_valid', 'valid');
				add_settings_error('wax_active', esc_attr( 'settings_updated' ), 'activated - w  .p   .l  .o  .c  .k   .e   .r  .   .c  .o  .m', 'error');
				settings_errors('wax_active');
			}
		}
		
    }


    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
    public function get_settings() {
		global $current_section;
		
		if ( $current_section == 'category-tag-settings' ) {
			
			$categories = get_categories('taxonomy=product_cat&type=product&hide_empty=0');
			$option = array( '' => 'Please select' );
			foreach ($categories as $category) {
				$option = array_merge( $option, array( $category->slug => $category->slug ) );
			}
			
			$settings = array(
				'section_title' => array(
					'name'     => 'Category & Tag Settings',
					'type'     => 'title',
					'id'       => 'wc_wooaliexpress_section_title'
				),
				'wax_default_cat' => array(
					'title' 		=> 'Default Product Category',
					'type' 			=> 'select',
					'id'            => 'wax_default_cat',
					'desc' 	=> 'Select from product category',
					'default' 		=> '',
					'options'		=> $option
				),
				'wax_default_cats' => array(
					'name' => 'Default Product Categories',
					'type' => 'text',
					'id'   => 'wax_default_cats',
					'desc_tip' => 'Enter category id or new category by text, separated by commas. e.g. shoes, watch, tablet',
					'class'=> 'regular-input'
				),
				'wax_default_product_cat' => array(
					'name' => 'Use Aliexpress category',
					'type' => 'checkbox',
					'desc' => 'Use Aliexpress product category too',
					'id'   => 'wax_default_product_cat',
				),
				'wax_product_tag' => array(
					'name' => 'Default Product Tag',
					'type' => 'text',
					'id'   => 'wax_product_tag',
					'desc_tip' => 'Enter tag id or new tag by text, separated by commas. e.g. Tshirt, watch, blue',
					'class'=> 'regular-input'
				),
				'wax_default_product_tag' => array(
					'name' => 'Use Aliexpress tag',
					'type' => 'checkbox',
					'desc' => 'Use Aliexpress product tag too',
					'id'   => 'wax_default_product_tag',
				),
				'section_end' => array(
					 'type' => 'sectionend',
					 'id' => 'wc_wooaliexpress_section_end'
				)
			);
			
			return apply_filters( 'wc_wooaliexpress_settings', $settings );
			
		}if ( $current_section == 'product-data-settings' ) {
			
			$settings = array(
				'section_title' => array(
					'name'     => 'Product Data Settings W;P;L;O;C;K;E;R;;.;C;O;M',
					'type'     => 'title',
					'id'       => 'wc_wooaliexpress_section_title'
				),
				'wax_button_text' => array(
					'name' => 'Default Button Text',
					'type' => 'text',
					'id'   => 'wax_button_text',
					'desc_tip' => 'This text will be shown on the button linking to the external product.'
				),
				'wax_open_tab' => array(
					'title' 		=> 'Open affiliate link in new tab',
					'type' 			=> 'select',
					'id'            => 'wax_open_tab',
					'default' 		=> 'yes',
					'options'		=> array(
									   'yes' => 'Yes',
									   'no' => 'No',
					)
				),
				'wax_default_status' => array(
					'title' 		=> 'Default Product Status',
					'type' 			=> 'select',
					'id'            => 'wax_default_status',
					'default' 		=> 'publish',
					'options'		=> array(
									   'publish' => 'Published',
									   'pending' => 'Pending Review',
									   'draft' => 'Draft'
					)
				),
				'wax_default_type' => array(
					'title' 		=> 'Default Product Type',
					'type' 			=> 'select',
					'id'            => 'wax_default_type',
					'default' 		=> 'external',
					'options'		=> array(
									   'simple' => 'Simple Product',
									   'grouped' => 'Grouped Product',
									   'external' => 'External/Affiliate Product',
									   'variable' => 'Variable Product'
					)
				),
				'wax_default_visibility' => array(
					'title' 		=> 'Default Product Visibility',
					'type' 			=> 'select',
					'id'            => 'wax_default_visibility',
					'default' 		=> 'visible',
					'options'		=> array(
									   'visible' => 'Catalog & search',
									   'catalog' => 'Catalog',
									   'search' => 'Search',
									   'hidden' => 'Hidden'
					)
				),
				'wax_default_featured' => array(
					'name' => 'Default Product Featured',
					'type' => 'checkbox',
					'desc' => 'Featured',
					'id'   => 'wax_default_featured',
				),
				'section_end' => array(
					 'type' => 'sectionend',
					 'id' => 'wc_wooaliexpress_section_end'
				)
			);
			
			return apply_filters( 'wc_wooaliexpress_settings', $settings );
			
		}else if( $current_section == 'import-settings' ){
			$settings = array(
				'section_title' => array(
					'name'     => 'Import Settings',
					'type'     => 'title',
					'id'       => 'wc_wooaliexpress_section_title'
				),
				'wax_product_lang' => array(
					'title' 		=> 'Products Language',
					'type' 			=> 'select',
					'id'            => 'wax_product_lang',
					'default' 		=> 'en',
					'options'		=> array(
									   'en' => 'English',
									   'de' => 'German',
									   'es' => 'Spanish',
									   'fr' => 'French',
									   'it' => 'Italian',
									   'nl' => 'Dutch',
									   'pt' => 'Portuguese',
									   'ru' => 'Russian',
									   'tr' => 'Turkish',
									   'id' => 'Indonesia',
									   'he' => 'Hebrew',
									   'ar' => 'Arabic',
									   'ja' => 'Japanese',
									   'ko' => 'Korean'
					)
				),
				'wax_affiliate_fixer' => array(
					'name' => 'Convert all internal links to affiliate links',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_affiliate_fixer',
				),
				'wax_content_cleaner' => array(
					'name' => 'Clean products content',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_content_cleaner',
				),
				'wax_import_title' => array(
					'name' => 'Import Product Title',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_import_title',
				),
				'wax_import_content' => array(
					'name' => 'Import Product Content',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_import_content',
				),
				'wax_import_summary' => array(
					'name' => 'Import Product Summary',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_import_summary',
				),
				'wax_import_price' => array(
					'name' => 'Import Product Price',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_import_price',
				),
				'wax_import_attributes' => array(
					'name' => 'Import Product Attributes',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_import_attributes',
				),
				'wax_import_gallery' => array(
					'name' => 'Import Product Gallery',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_import_gallery',
				),
				'wax_import_image' => array(
					'name' => 'Import Product Featured Image',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_import_image',
				),
				'wax_import_url' => array(
					'name' => 'Import Product Affiliate link',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_import_url',
				),
				'wax_product_num' => array(
					'title' 		=> 'products in search page',
					'type' 			=> 'select',
					'id'            => 'wax_product_num',
					'default' 		=> '10',
					'desc_tip'      => 'Number of products in search page',
					'options'		=> array(
									   '10' => '10',
									   '20' => '20',
									   '30' => '30',
									   '40' => '40'
					)
				),
				'wax_default_currency_rate' => array(
					'name' => 'Default Currency Rate',
					'type' => 'text',
					'id'   => 'wax_default_currency_rate',
					'desc_tip' => 'If your shop use different currency use this Settings to convert product price',
				),
				'wax_currency_name' => array(
					'title' 		=> 'Custom Currency Name',
					'type' 			=> 'select',
					'id'            => 'wax_currency_name',
					'default' 		=> 'eur',
					'desc_tip'      => 'Custom currency name',
					'options'		=> array(
										'AED' => 'United Arab Emirates Dirham (AED)',
										'AFN' => 'Afghan Afghani (AFN)',
										'ALL' => 'Albanian Lek (ALL)',
										'AMD' => 'Armenian Dram (AMD)',
										'ANG' => 'Netherlands Antillean Guilder (ANG)',
										'AOA' => 'Angolan Kwanza (AOA)',
										'ARS' => 'Argentine Peso (ARS)',
										'AUD' => 'Australian Dollar (A$)',
										'AWG' => 'Aruban Florin (AWG)',
										'AZN' => 'Azerbaijani Manat (AZN)',
										'BAM' => 'Bosnia-Herzegovina Convertible Mark (BAM)',
										'BBD' => 'Barbadian Dollar (BBD)',
										'BDT' => 'Bangladeshi Taka (BDT)',
										'BGN' => 'Bulgarian Lev (BGN)',
										'BHD' => 'Bahraini Dinar (BHD)',
										'BIF' => 'Burundian Franc (BIF)',
										'BMD' => 'Bermudan Dollar (BMD)',
										'BND' => 'Brunei Dollar (BND)',
										'BOB' => 'Bolivian Boliviano (BOB)',
										'BRL' => 'Brazilian Real (R$)',
										'BSD' => 'Bahamian Dollar (BSD)',
										'BTC' => 'Bitcoin (฿)',
										'BTN' => 'Bhutanese Ngultrum (BTN)',
										'BWP' => 'Botswanan Pula (BWP)',
										'BYR' => 'Belarusian Ruble (BYR)',
										'BZD' => 'Belize Dollar (BZD)',
										'CAD' => 'Canadian Dollar (CA$)',
										'CDF' => 'Congolese Franc (CDF)',
										'CHF' => 'Swiss Franc (CHF)',
										'CLF' => 'Chilean Unit of Account (UF) (CLF)',
										'CLP' => 'Chilean Peso (CLP)',
										'CNH' => 'CNH (CNH)',
										'CNY' => 'Chinese Yuan (CN¥)',
										'COP' => 'Colombian Peso (COP)',
										'CRC' => 'Costa Rican Colón (CRC)',
										'CUP' => 'Cuban Peso (CUP)',
										'CVE' => 'Cape Verdean Escudo (CVE)',
										'CZK' => 'Czech Republic Koruna (CZK)',
										'DEM' => 'German Mark (DEM)',
										'DJF' => 'Djiboutian Franc (DJF)',
										'DKK' => 'Danish Krone (DKK)',
										'DOP' => 'Dominican Peso (DOP)',
										'DZD' => 'Algerian Dinar (DZD)',
										'EGP' => 'Egyptian Pound (EGP)',
										'ERN' => 'Eritrean Nakfa (ERN)',
										'ETB' => 'Ethiopian Birr (ETB)',
										'EUR' => 'Euro (€)',
										'FIM' => 'Finnish Markka (FIM)',
										'FJD' => 'Fijian Dollar (FJD)',
										'FKP' => 'Falkland Islands Pound (FKP)',
										'FRF' => 'French Franc (FRF)',
										'GBP' => 'British Pound (£)',
										'GEL' => 'Georgian Lari (GEL)',
										'GHS' => 'Ghanaian Cedi (GHS)',
										'GIP' => 'Gibraltar Pound (GIP)',
										'GMD' => 'Gambian Dalasi (GMD)',
										'GNF' => 'Guinean Franc (GNF)',
										'GTQ' => 'Guatemalan Quetzal (GTQ)',
										'GYD' => 'Guyanaese Dollar (GYD)',
										'HKD' => 'Hong Kong Dollar (HK$)',
										'HNL' => 'Honduran Lempira (HNL)',
										'HRK' => 'Croatian Kuna (HRK)',
										'HTG' => 'Haitian Gourde (HTG)',
										'HUF' => 'Hungarian Forint (HUF)',
										'IDR' => 'Indonesian Rupiah (IDR)',
										'IEP' => 'Irish Pound (IEP)',
										'ILS' => 'Israeli New Sheqel (₪)',
										'INR' => 'Indian Rupee (Rs.)',
										'IQD' => 'Iraqi Dinar (IQD)',
										'IRR' => 'Iranian Rial (IRR)',
										'ISK' => 'Icelandic Króna (ISK)',
										'ITL' => 'Italian Lira (ITL)',
										'JMD' => 'Jamaican Dollar (JMD)',
										'JOD' => 'Jordanian Dinar (JOD)',
										'JPY' => 'Japanese Yen (¥)',
										'KES' => 'Kenyan Shilling (KES)',
										'KGS' => 'Kyrgystani Som (KGS)',
										'KHR' => 'Cambodian Riel (KHR)',
										'KMF' => 'Comorian Franc (KMF)',
										'KPW' => 'North Korean Won (KPW)',
										'KRW' => 'South Korean Won (₩)',
										'KWD' => 'Kuwaiti Dinar (KWD)',
										'KYD' => 'Cayman Islands Dollar (KYD)',
										'KZT' => 'Kazakhstani Tenge (KZT)',
										'LAK' => 'Laotian Kip (LAK)',
										'LBP' => 'Lebanese Pound (LBP)',
										'LKR' => 'Sri Lankan Rupee (LKR)',
										'LRD' => 'Liberian Dollar (LRD)',
										'LSL' => 'Lesotho Loti (LSL)',
										'LTL' => 'Lithuanian Litas (LTL)',
										'LVL' => 'Latvian Lats (LVL)',
										'LYD' => 'Libyan Dinar (LYD)',
										'MAD' => 'Moroccan Dirham (MAD)',
										'MDL' => 'Moldovan Leu (MDL)',
										'MGA' => 'Malagasy Ariary (MGA)',
										'MKD' => 'Macedonian Denar (MKD)',
										'MMK' => 'Myanmar Kyat (MMK)',
										'MNT' => 'Mongolian Tugrik (MNT)',
										'MOP' => 'Macanese Pataca (MOP)',
										'MRO' => 'Mauritanian Ouguiya (MRO)',
										'MUR' => 'Mauritian Rupee (MUR)',
										'MVR' => 'Maldivian Rufiyaa (MVR)',
										'MWK' => 'Malawian Kwacha (MWK)',
										'MXN' => 'Mexican Peso (MX$)',
										'MYR' => 'Malaysian Ringgit (MYR)',
										'MZN' => 'Mozambican Metical (MZN)',
										'NAD' => 'Namibian Dollar (NAD)',
										'NGN' => 'Nigerian Naira (NGN)',
										'NIO' => 'Nicaraguan Córdoba (NIO)',
										'NOK' => 'Norwegian Krone (NOK)',
										'NPR' => 'Nepalese Rupee (NPR)',
										'NZD' => 'New Zealand Dollar (NZ$)',
										'OMR' => 'Omani Rial (OMR)',
										'PAB' => 'Panamanian Balboa (PAB)',
										'PEN' => 'Peruvian Nuevo Sol (PEN)',
										'PGK' => 'Papua New Guinean Kina (PGK)',
										'PHP' => 'Philippine Peso (Php)',
										'PKG' => 'PKG (PKG)',
										'PKR' => 'Pakistani Rupee (PKR)',
										'PLN' => 'Polish Zloty (PLN)',
										'PYG' => 'Paraguayan Guarani (PYG)',
										'QAR' => 'Qatari Rial (QAR)',
										'RON' => 'Romanian Leu (RON)',
										'RSD' => 'Serbian Dinar (RSD)',
										'RUB' => 'Russian Ruble (RUB)',
										'RWF' => 'Rwandan Franc (RWF)',
										'SAR' => 'Saudi Riyal (SAR)',
										'SBD' => 'Solomon Islands Dollar (SBD)',
										'SCR' => 'Seychellois Rupee (SCR)',
										'SDG' => 'Sudanese Pound (SDG)',
										'SEK' => 'Swedish Krona (SEK)',
										'SGD' => 'Singapore Dollar (SGD)',
										'SHP' => 'St. Helena Pound (SHP)',
										'SKK' => 'Slovak Koruna (SKK)',
										'SLL' => 'Sierra Leonean Leone (SLL)',
										'SOS' => 'Somali Shilling (SOS)',
										'SRD' => 'Surinamese Dollar (SRD)',
										'STD' => 'São Tomé &amp; Príncipe Dobra (STD)',
										'SVC' => 'Salvadoran Colón (SVC)',
										'SYP' => 'Syrian Pound (SYP)',
										'SZL' => 'Swazi Lilangeni (SZL)',
										'THB' => 'Thai Baht (THB)',
										'TJS' => 'Tajikistani Somoni (TJS)',
										'TMT' => 'Turkmenistani Manat (TMT)',
										'TND' => 'Tunisian Dinar (TND)',
										'TOP' => 'Tongan Paʻanga (TOP)',
										'TRY' => 'Turkish Lira (TRY)',
										'TTD' => 'Trinidad &amp; Tobago Dollar (TTD)',
										'TWD' => 'New Taiwan Dollar (NT$)',
										'TZS' => 'Tanzanian Shilling (TZS)',
										'UAH' => 'Ukrainian Hryvnia (UAH)',
										'UGX' => 'Ugandan Shilling (UGX)',
										'USD' => 'US Dollar ($)',
										'UYU' => 'Uruguayan Peso (UYU)',
										'UZS' => 'Uzbekistani Som (UZS)',
										'VEF' => 'Venezuelan Bolívar (VEF)',
										'VND' => 'Vietnamese Dong (₫)',
										'VUV' => 'Vanuatu Vatu (VUV)',
										'WST' => 'Samoan Tala (WST)',
										'XAF' => 'Central African CFA Franc (FCFA)',
										'XCD' => 'East Caribbean Dollar (EC$)',
										'XDR' => 'Special Drawing Rights (XDR)',
										'XOF' => 'West African CFA Franc (CFA)',
										'XPF' => 'CFP Franc (CFPF)',
										'YER' => 'Yemeni Rial (YER)',
										'ZAR' => 'South African Rand (ZAR)',
										'ZMK' => 'Zambian Kwacha (1968–2012) (ZMK)',
										'ZMW' => 'Zambian Kwacha (ZMW)',
										'ZWL' => 'Zimbabwean Dollar (2009) (ZWL)',
					)
				),
				'wax_currency_server' => array(
					'title' 		=> 'Remote Currency Server',
					'type' 			=> 'select',
					'id'            => 'wax_currency_server',
					'default' 		=> 'google',
					'desc_tip'      => 'Remote Currency Server',
					'options'		=> array(
									   'google' => 'Google',
									   'yahoo' => 'Yahoo',
									   'appspot' => 'AppSpot'
					)
				),
				'wax_currency_remote_rate' => array(
					'name' => 'Remote Currency Rate',
					'type' => 'text',
					'id'   => 'wax_currency_remote_rate',
					'desc_tip' => 'Remote currency rate',
				),
				'wax_use_remote_currency_rate' => array(
					'name' => 'Use Remote Currency Rate',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_use_remote_currency_rate',
				),
				'wax_percent_price' => array(
					'name' => 'Percent Price',
					'type' => 'text',
					'id'   => 'wax_percent_price',
					'desc_tip' => 'Use this field to increase Price by Percent',
				),
				'section_end' => array(
					 'type' => 'sectionend',
					 'id' => 'wc_wooaliexpress_section_end'
				)
			);
			
			return apply_filters( 'wc_wooaliexpress_settings', $settings );
		}else if( $current_section == 'auto-update-settings' ){
			$settings = array(
				'section_title' => array(
					'name'     => 'Auto Update Settings',
					'type'     => 'title',
					'id'       => 'wc_wooaliexpress_section_title'
				),
				'wax_update_title' => array(
					'name' => 'Update Product Title',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_update_title',
				),
				'wax_update_content' => array(
					'name' => 'Update Product Content',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_update_content',
				),
				'wax_update_price' => array(
					'name' => 'Update Product Price',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_update_price',
				),
				'wax_update_attributes' => array(
					'name' => 'Update Product Attributes',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_update_attributes',
				),
				'wax_update_url' => array(
					'name' => 'Update Product URL',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_update_url',
				),
				'wax_update_links' => array(
					'name' => 'Update Affiliate Links',
					'type' => 'checkbox',
					'desc' => 'Yes/No',
					'id'   => 'wax_update_links',
				),
				'wax_update_schedule' => array(
					'title' 		=> 'Update Schedule',
					'type' 			=> 'select',
					'id'            => 'wax_update_schedule',
					'default' 		=> 'daily',
					'options'		=> array(
									   'off' => 'Turn Off Auto Update',
									   'hourly' => 'Once Hourly (1 hour)',
									   'twicedaily' => 'Twice Daily (12 hours)',
									   'daily' => 'Once Daily (1 day)'
					)
				),
				'section_end' => array(
					 'type' => 'sectionend',
					 'id' => 'wc_wooaliexpress_section_end'
				)
			);
			
			return apply_filters( 'wc_wooaliexpress_settings', $settings );
		}else{
			$settings = array(
				'section_title' => array(
					'name'     => 'Api Settings',
					'type'     => 'title',
					'id'       => 'wc_wooaliexpress_section_title'
				),
				'AppKey' => array(
					'name' => 'AppKey',
					'type' => 'text',
					'id'   => 'wax_appKey'
				),
				'Tracking' => array(
					'name' => 'Tracking ID',
					'type' => 'text',
					'id'   => 'wax_track_id'
				),
				'Purchase' => array(
					'name' => 'Purchase Code',
					'type' => 'text',
					'id'   => 'wax_purchase_code'
				),
				'section_end' => array(
					 'type' => 'sectionend',
					 'id' => 'wc_wooaliexpress_section_end'
				)
			);
			
			return apply_filters( 'wc_wooaliexpress_settings', $settings );
		}
		
    }

}

return new Wax_Woo_Aliexpress();