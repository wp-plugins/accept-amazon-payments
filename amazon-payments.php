<?php
/*
Plugin Name: Amazon Payments
Plugin URI: https://www.tipsandtricks-hq.com/amazon-payments-plugin-for-wordpress
Description: Amazon Payments plugin allows you to create payment buttons to accept payment via Amazon.
Author: Tips and Tricks HQ, bestwebsoft
Version: 1.0
Author URI: https://www.tipsandtricks-hq.com/
License: GPLv2 or later
*/

/*
** Function for adding menu and submenu
*/
if ( ! function_exists( 'amznpmnts_admin_menu' ) ) {
	function amznpmnts_admin_menu() {
		add_menu_page( __( 'Amazon Orders', 'amazon-payments' ), __( 'Amazon Orders', 'amazon-payments' ), 'manage_options', 'amznpmnts_manager', 'amznpmnts_manager_page', 'dashicons-admin-post', '39' );
		add_submenu_page( 'options-general.php',  __( 'Amazon Payments', 'amazon-payments' ),  __( 'Amazon Payments', 'amazon-payments' ), 'manage_options', 'amznpmnts_settings', 'amznpmnts_settings_page' );
	}
}


if ( ! function_exists( 'amznpmnts_init' ) ) {
	function amznpmnts_init() {
		global $wpdb, $prefix;
		$prefix = $wpdb->prefix . 'amznpmnts_';
		if( isset( $_GET['buyerName'] ) && isset( $_GET['status'] ) ) {
			$wpdb->insert( $prefix . 'buyer_info', array(
				'paymentReason'		=> stripcslashes( ( $_GET['paymentReason'] ) ),
				'transactionAmount'	=> stripcslashes( ( $_GET['transactionAmount'] ) ),
				'transactionId'		=> stripcslashes( ( $_GET['transactionId'] ) ),
				'status_pay'		=> stripcslashes( ( $_GET['status'] ) ),
				'buyerEmail'		=> stripcslashes( ( $_GET['buyerEmail'] ) ),
				'referenceId'		=> stripcslashes( ( $_GET['referenceId'] ) ),
				'transactionDate'	=> stripcslashes( ( $_GET['transactionDate'] ) ),
				'buyerName'			=> stripcslashes( ( $_GET['buyerName'] ) ),
				'status'			=> stripcslashes( ( $_GET['operation'] ) ),
				'paymentMethod'		=> stripcslashes( ( $_GET['paymentMethod'] ) ),
				'certificateUrl'	=> stripcslashes( ( $_GET['certificateUrl'] ) ),
				'status_id'			=> 1
			));
		}
		/* 
		** Function '_plugin_init' are using to add language files. 
		*/
		load_plugin_textdomain( 'amazon-payments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}
}

if ( ! function_exists( 'amznpmnts_admin_init' ) ) {
	function amznpmnts_admin_init() {
		/*Initialization*/
		if ( isset( $_REQUEST['page'] ) && 'amznpmnts_manager' == $_REQUEST['page'] )
			amznpmnts_action_links();
	}
}

/*
* Function to add actions link to block with plugins name on "Plugins" page 
*/
if ( ! function_exists( 'amznpmnts_plugin_action_links' ) ) {
	function amznpmnts_plugin_action_links( $links, $file ) {
		static $this_plugin;
		if ( ! $this_plugin ) 
			$this_plugin = plugin_basename( __FILE__ );
		if ( $file == $this_plugin ) {
				$settings_link = '<a href="options-general.php?page=amznpmnts_settings">' . __( 'Settings', 'amazon-payments' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		return $links;
	}
}

/*
* Function to add links to description block on "Plugins" page 
*/
if ( ! function_exists( 'amznpmnts_register_plugin_links' ) ) {
	function amznpmnts_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			$links[] = '<a href="admin.php?page=amznpmnts_settings">' . __( 'Settings','amazon-payments' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists( 'amznpmnts_cunstruct_class' ) ) {
	function amznpmnts_cunstruct_class() {
		require_once 'ButtonGenerator.php';
		global $file, $amount, $description, $currency, $url;
		class StandardButtonSample {
					 
			private static $accessKey 				= "AKIAI3RCRXE6AJQPNT7A";				//Put your Access Key here
			private static $secretKey 				= "faSzg5UL5+fFxVTYndtmjY7Xxyl49qpUb4hMN/EM";			//Put  your Secret Key here
			private static $amount 					= "USD 12";						//Enter the amount you want to collect for the item
			private static $signatureMethod 		= "HmacSHA256"; 					// Valid values  are  HmacSHA256 and HmacSHA1.
			private static $description 			= "Test";					 //Enter a description of the item
			private static $referenceId				= "test-reference123"; 				 //Optionally, enter an ID that uniquely identifies this transaction for your records
			private static $abandonUrl				= "";		 //Optionally, enter the URL where senders should be redirected if they cancel their transaction
			private static $returnUrl				= "";			 //Optionally enter the URL where buyers should be redirected after they complete the transaction
			private static $immediateReturn			= "0"; 						 //Optionally, enter "1" if you want to skip the final status page in Amazon Payments
			private static $processImmediate		= "1"; 						 //Optionally, enter "1" if you want to settle the transaction immediately else "0". Default value is "1" 
			private static $ipnUrl					= "";				 //Optionally, type the URL of your host page to which Amazon Payments should send the IPN transaction information.
			private static $collectShippingAddress	= null;					 //Optionally, enter "1" if you want Amazon Payments to return the buyer's shipping address as part of the transaction information
			private static $environment				= "sandbox"; 					//Valid values are "sandbox" or "prod"*/

			public static function Sampleform() {
				global $amount, $description, $currency, $options, $prefix, $wpdb, $url;
				$prefix	 = $wpdb->prefix . 'amznpmnts_'; 
				$options = $wpdb->get_results( "SELECT * FROM `" . $prefix . "amazon_sittings` WHERE id = '1' ");

				self::$amount 		= $currency . " " . $amount;
				self::$description 	= $description;
				
				foreach ( $options as $key => $value ) { 
					$options[$key] 		= $value;
					self::$abandonUrl 	= $options[$key]->url;
					self::$returnUrl 	= $options[$key]->returnUrl;
					self::$ipnUrl 		= $options[$key]->ipnUrl;
					self::$accessKey 	= $options[$key]->access_key;
					self::$secretKey 	= $options[$key]->secret_key;
					self::$environment 	= $options[$key]->environment;
				}
				try{
					ButtonGenerator::GenerateForm( self::$accessKey,self::$secretKey,self::$amount, self::$description, self::$referenceId, self::$immediateReturn,self::$returnUrl, self::$abandonUrl, self::$processImmediate, self::$ipnUrl, self::$collectShippingAddress,self::$signatureMethod, self::$environment );
				}
				catch( Exception $e ){
					echo 'Exception : ', $e->getMessage(),"\n";
				}
			}
		}
	}
}

if ( ! function_exists( 'amznpmnts_action' ) ) {
	function amznpmnts_action( $atts ){
		global $file, $amount, $description, $currency; 
		extract( shortcode_atts( array( 'amount' => '10', 'currency' => 'USD', 'description' => 'test' ), $atts ) );
		StandardButtonSample::SampleForm();
	}
}

/* Activation plugin function */
if ( ! function_exists( 'amznpmnts_plugin_activate' ) ) {
	function amznpmnts_plugin_activate( $networkwide ) {
		global $wpdb;
		/* Activation function for network */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			/* check if it is a network activation - if so, run the activation function for each blog id */
			if ( $networkwide ) {
				$old_blog = $wpdb->blogid;
				/* Get all blog ids */
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					amznpmnts_create_table();
				}
				switch_to_blog( $old_blog );
				return;
			}
		}
		amznpmnts_create_table();
	}
}

/*
** Function add table for database.
*/
if ( ! function_exists( 'amznpmnts_create_table' ) ) {
	function amznpmnts_create_table() {
		global $wpdb, $prefix;
		$prefix = $wpdb->prefix . 'amznpmnts_';
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$sql = "CREATE TABLE IF NOT EXISTS `" . $prefix . "buyer_info` (
			`id` INT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
			`buyerName` varchar(64) NOT NULL,
			`buyerEmail` varchar(64) NOT NULL,
			`paymentReason` varchar(64) NOT NULL,
			`transactionAmount` varchar(64) NOT NULL,
			`transactionId` varchar(64) NOT NULL,
			`status_pay` varchar(64) NOT NULL,
			`referenceId` varchar(64) NOT NULL,
			`transactionDate` INT(6) NOT NULL,
			`status` varchar(64) NOT NULL,
			`paymentMethod` varchar(64) NOT NULL,
			`certificateUrl` varchar(64) NOT NULL,
			`status_id` INT(6) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		dbDelta( $sql );
		$sql = "CREATE TABLE IF NOT EXISTS `" . $prefix . "amazon_sittings` (
			`id` INT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
			`access_key` varchar(64) NOT NULL,
			`secret_key` varchar(64) NOT NULL,
			`url` varchar(64) NOT NULL,
			`returnUrl` varchar(64) NOT NULL,
			`ipnUrl` varchar(64) NOT NULL,
			`environment` varchar(64) NOT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		dbDelta( $sql );
		$options = $wpdb->get_results( "SELECT * FROM `" . $prefix . "amazon_sittings` WHERE id = '1' ");
		if ( empty( $options ) ) {
			$wpdb->insert( $prefix . 'amazon_sittings', array(
				'access_key'	=> "",
				'secret_key'	=> "",
				'url'			=> "",
				'returnUrl'		=> "",
				'ipnUrl'		=> "",
				'environment'	=> ""
			));
		}
	}
}

/*
** Function for displaying settings page of plugin.
*/
if ( ! function_exists( 'amznpmnts_settings_page' ) ) {
	function amznpmnts_settings_page() {
		global $wpdb, $prefix, $options; 
		$prefix = $wpdb->prefix . 'amznpmnts_';
		// set value of input type="hidden" when options is changed
		if( isset( $_POST['access_key'] ) ) {
			$wpdb->update( $prefix . 'amazon_sittings', array(
				'access_key'	=> isset( $_POST['access_key'] ) ? $_POST['access_key'] : "",
				'secret_key'	=> isset( $_POST['secret_key'] ) ? $_POST['secret_key'] : "",
				'url'			=> isset( $_POST['url'] ) ? $_POST['url'] : "",
				'returnUrl'		=> isset( $_POST['returnUrl'] ) ? $_POST['returnUrl'] : "",
				'ipnUrl'		=> isset( $_POST['ipnUrl'] ) ? $_POST['ipnUrl'] : "",
				'environment'	=> isset( $_POST['environment'] ) ? $_POST['environment'] : ""
			),
			array('ID' => 1 ));
		}
		$options = $wpdb->get_results( "SELECT * FROM `" . $prefix . "amazon_sittings` WHERE id = '1' ");
		foreach ($options as $key => $value) { 
			$options[$key] = $value; ?>
			<h2><?php _e( "Amazon Payment Settings", 'amazon-payments' ); ?></h2>

			<div class="wrap">
				<form id="amznpmnts_settings_form" method="post" action="options-general.php?page=amznpmnts_settings">
					<div class="amznpmnts-content">
						<div id="amznpmnts_settings_notice" class="updated fade" style="display:none">
							<p>
								<strong><?php _e( "Notice:", 'amazon-payments' ); ?></strong> <?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'amazon-payments' ); ?>
							</p>
						</div>
						<span style="margin-bottom:15px;">
                                                    <p>Read the plugin <a href="https://www.tipsandtricks-hq.com/amazon-payments-plugin-for-wordpress" target="_blank">usage documentation</a> to learn how to use it.</p>							
						</span>
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th>
										<?php _e( "Access Key", 'amazon-payments' ); ?>:
									</th>
									<td>
										<input id="amznpmnts-app-id" type="text" size="40" maxlength="32" value="<?php if ( isset( $options[$key]->access_key ) ){ echo $options[$key]->access_key; } ?>" name="access_key">
										<p class="description"><?php _e( "An application identifier associates your site, and Amazon application.", 'amazon-payments' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th>
										<?php _e( "Secret Key", 'amazon-payments' ); ?>:
									</th>
									<td>
										<input id="amznpmnts-app-secret" type="text" size="40" value="<?php if ( isset( $options[$key]->secret_key ) ){ echo $options[$key]->secret_key; } ?>" name="secret_key">
										<p class="description"><?php _e( "The secret code from Amazon.", 'amazon-payments' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th>
										<?php _e( "Abandon Url", 'amazon-payments' ); ?>:
									</th>
									<td>
										<input id="amznpmnts-url" type="text" size="40" value="<?php if ( isset( $options[$key]->url ) ){ echo $options[$key]->url; } ?>" name="url">
										<p class="description"><?php _e( "Optionally, enter the URL where senders should be redirected if they cancel their transaction.", 'amazon-payments' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th>
										<?php _e( "URL if transaction the complete", 'amazon-payments' ); ?>:
									</th>
									<td>
										<input id="amznpmnts-url" type="text" size="40" value="<?php if ( isset( $options[$key]->returnUrl ) ){ echo $options[$key]->returnUrl; } ?>" name="returnUrl">
										<p class="description"><?php _e( "Optionally enter the URL where buyers should be redirected after they complete the transaction.", 'amazon-payments' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th>
										<?php _e( "Ipn Url", 'amazon-payments' ); ?>:
									</th>
									<td>
										<input id="amznpmnts-url" type="text" size="40" value="<?php if ( isset( $options[$key]->ipnUrl ) ){ echo $options[$key]->ipnUrl; } ?>" name="ipnUrl">
										<p class="description"><?php _e( "Optionally, type the URL of your host page to which Amazon Payments should send the IPN transaction information.", 'amazon-payments' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th>
										<?php _e( "Environment", 'amazon-payments' ); ?>:
									</th>
									<td>
										<select name="environment">
											<option  <?php if ( '' == $options[$key]->environment ) echo "selected=\"selected\" "; ?>><?php _e( "Create a environment", 'amazon-payments' ); ?></option>
											<option value="prod" <?php if ( 'prod' == $options[$key]->environment ) echo "selected=\"selected\" "; ?>>prod</option>
											<option value="sandbox" <?php if ( 'sandbox' == $options[$key]->environment ) echo "selected=\"selected\" "; ?>>sandbox</option>
										</select>
										<p class="description"><?php _e( "If desired, you can switch between the Sandbox and products", 'amazon-payments' ); ?></p>
									</td>
								</tr>
							</tbody>
						</table><!-- .form-table -->
					</div><!-- .amznpmnts-content -->
					<input type="submit" id="submit_options" class="button-primary" value="<?php _e( 'Save all changes', 'amazon-payments'  ); ?>" />
				</form><!-- #amznpmnts_settings_form -->	
				<br class="clear">	
			</div><!-- .wrap -->
		<?php }
	}
}




/*
** Function to handle action links.
*/
if ( ! function_exists( 'amznpmnts_action_links' ) ) {
	function amznpmnts_action_links() {
		global $wpdb, $action, $counter, $amznpmnts_done, $amznpmnts_error;
		$error_counter = 0;
		$prefix = $wpdb->prefix . 'amznpmnts_';
		if ( isset( $_REQUEST['action'] ) || isset( $_REQUEST['action2'] ) ) {
			$ids = '';
			if ( isset( $_REQUEST['action'] ) )
				$action = $_REQUEST['action'];
			else
				$action = $_REQUEST['action2'];
			if ( isset( $_REQUEST['buyer_id'] ) && '' !=  $_REQUEST['buyer_id'] ) {
				// when action is "undo", "restore" or "spam" - order id`s is a string like "2,3,4,5,6,"
				if ( preg_match( '|,|', $_REQUEST['buyer_id'][0] ) ) 
					$ids = explode(  ',', $_REQUEST['buyer_id'][0] );
				if ( '' != $ids ) {
					$buyer_id = $ids;
				} else {
					$buyer_id = $_REQUEST['buyer_id'];
				};
				foreach ( $buyer_id as $id ) {
					if ( '' != $id ) {
						switch ( $action ) {
							case 'delete':
							case 'deletes':
								// delete all records about choosen order from database 
								$error = 0;
								$wpdb->query( "DELETE FROM `" . $prefix . "buyer_info` WHERE " . $prefix . "buyer_info.id=" . $id );
								$error += $wpdb->last_error ? 1 : 0;
								if ( 0 == $error ) {
									$counter++;
								} else {
									$error_counter++;
								}
								break;
							// marking orders as Trash*/
							case 'trash':
								$wpdb->update( $prefix . 'buyer_info', array( 'status_id' => 2 ), array( 'id' => $id ) );
								if ( ! 0 == $wpdb->last_error ) 
									$error_counter ++; 
								else
									$counter ++;
								break;
							case 'restore':
							case 'undo':
								$wpdb->update( $prefix . 'buyer_info', array( 'status_id' => 1 ), array( 'id' => $id ) );
								if ( ! 0 == $wpdb->last_error ) {
									$error_counter ++; 
								} else {
									$counter ++;
								}
								break;
							default:
								$unknown_action = 1;
							break;
						}
					}
					switch ( $action ) {
						case 'delete':
						case 'deletes':
							if ( 0 == $error_counter ) {
								$amznpmnts_done =  sprintf( _n( 'One order was delete successfully', '%s order was delete successfully.', $counter, 'amazon-payments' ), number_format_i18n( $counter ) );
							} else { 
								$amznpmnts_error = __( 'There are some problems while deleting order.', 'amazon-payments' );
							}
							break;
						case 'trash':
							$ids = '';
							if ( 0 == $error_counter ) {
								if ( 1 < count( $buyer_id ) ) {
									// get ID`s of order to string in format "1,2,3,4,5" to add in action link
									foreach( $buyer_id as $value )
										$ids .= $value . ',';
								} else {
									$ids = $buyer_id['0'];
								}
								$amznpmnts_done =  sprintf( _n( 'One order was moved to Trash.', '%s order was moved to Trash.', $counter, 'amazon-payments' ), number_format_i18n( $counter ) ); 
								$amznpmnts_done .= ' <a href="?page=amznpmnts_manager&action=undo&buyer_id[]=' . $ids . '">' . __( 'Undo', 'amazon-payments' ) . '</a>';
							} else {
								$amznpmnts_error .= __( "Problems while moving order to Trash.", "amazon-payments" ) . __( " Please, try it later.", "amazon-payments" ); 
							 }
							break;
						case 'restore':
						case 'undo':
							if ( 0 == $error_counter ) {
								$amznpmnts_done = sprintf ( _n( 'One order was restored.', '%s order was restored.', $counter, 'amazon-payments' ), number_format_i18n( $counter ) );
								
							 } else {
								$amznpmnts_error = __( 'Problems during the restoration order', 'amazon-payments' ); 
							 }
							break;
						default:
							$unknown_action = 1;
						break;
					}
				} // end of foreach
			}
		}
	}
}

/*
** Function to get data in order list
*/
if ( ! function_exists( 'amznpmnts_get_list' ) ) {
	function amznpmnts_get_list() {
		global $wpdb;
		$prefix = $wpdb->prefix . 'amznpmnts_';
		$start_row = 0;
		if ( isset( $_REQUEST['paged'] ) && '1' != $_REQUEST['paged'] ) {
			$start_row = 5 * ( absint( $_REQUEST['paged'] - 1 ) );
		}
		$sql_query = "SELECT * FROM " . $prefix . "buyer_info ";
		if ( isset( $_REQUEST['buyer_status'] ) ) { // depending on request display different list of buyer
			if ( 'all' == $_REQUEST['buyer_status'] ) {
				$sql_query .= "WHERE " . $prefix . "buyer_info.status_id='1'";
			} elseif ( 'trash' == $_REQUEST['buyer_status'] ) {
				$sql_query .= "WHERE " . $prefix . "buyer_info.status_id='2'";
			}
		} else {
			$sql_query .= "WHERE " . $prefix . "buyer_info.status_id='1'";
		}
		$sql_query .= " ORDER BY buyerName DESC LIMIT 5 OFFSET " . $start_row;
		$buyer = $wpdb->get_results( $sql_query);
		$i = 0;
		$attachments_icon = '';
		$list_of_buyer = array();
		
		foreach ( $buyer as $value ) { 
			// fill "status" column 
			$the_buyer_status_trash = '<a href="?page=amznpmnts_manager&action=trash&buyer_id[]='. $value->id .'">';
			if ( '1' == $value->status_id )
				$the_buyer_status_trash .= '<div class="amznpmnts-letter" title="'. __( 'Mark as Trash', 'amazon-payments' ) . '">' . $value->status_id . '</div>';				
			else
				$the_buyer_status_trash .= '<div class="amznpmnts-trash" title="'. __( 'Trash status', 'amazon-payments' ) . '">' . $value->status_id . '</div>';
			$the_buyer_status_trash .= '</a>';
			$buyerInfo = '<div class="buyer-name" >
				<div class="amznpmnts-text">';
				if ( '' !=  $value->buyerName )
					$buyerInfo .= $value->buyerName. ' - ' .$value->buyerEmail .'</div>';
				else
					$buyerInfo .= '<i>' . __( '- Unknown orders info - ', 'amazon-payments' ) . '</i>';
			$buyerInfo .= '</div>';
			// fill "from" column
			$paymentReason = '<div class="description">
				<div class="amznpmnts-text">';
					if ( '' !=  $value->paymentReason ){
						$paymentReason .= $value->paymentReason . '</div>';
					
				} else {
					$paymentReason .= '<i>' . __( ' - No payment - ', 'amazon-payments' ) . '</i></div>';
				}
			$paymentReason .= '</div>';
			$transactionAmount = '<div class="buyer-attachment">
				<div class="amznpmnts-text">';
				if ( '' !=  $value->transactionAmount ) 
					$transactionAmount .= $value->transactionAmount . '</div>';
				else
					$transactionAmount .= '<i>' . __( ' - No amount in this order - ', 'amazon-payments' ) . '</i></div>';
			$transactionAmount .= '</div>';
			$paymentMethod = '<div class="buyer-name" >
				<div class="amznpmnts-text">';
				if ( '' !=  $value->paymentMethod )
					$paymentMethod .= $value->paymentMethod.'</div>';
				else
					$paymentMethod .= '<i>' . __( '- Unknown buyer name-', 'amazon-payments' ) . '</i>';
			$paymentMethod .= '</div>';
			$status_pay = '<div class="buyer-name" >
				<div class="amznpmnts-text">';
				if ( '' !=  $value->status_pay )
					$status_pay .= $value->status_pay.'</div>';
				else
					$status_pay .= '<i>' . __( '- Unknown buyer name-', 'amazon-payments' ) . '</i>';
			$status_pay .= '</div>';
			$referenceId = '<div class="buyer-name" >
				<div class="amznpmnts-text">';
				if ( '' !=  $value->referenceId )
					$referenceId .= $value->referenceId.'</div>';
				else
					$referenceId .= '<i>' . __( '- Unknown buyer name-', 'amazon-payments' ) . '</i>';
			$referenceId .= '</div>';
			$transactionDate = '<div class="buyer-name" >
				<div class="amznpmnts-text">';
				if ( '' !=  $value->transactionDate )
					$transactionDate .= $value->transactionDate.'</div>';
				else
					$transactionDate .= '<i>' . __( '- Unknown buyer name-', 'amazon-payments' ) . '</i>';
			$transactionDate .= '</div>';
			$status = '<div class="buyer-name" >
				<div class="amznpmnts-text">';
				if ( '' !=  $value->status )
					$status .= $value->status.'</div>';
				else
					$status .= '<i>' . __( '- Unknown buyer name-', 'amazon-payments' ) . '</i>';
			$status .= '</div>';

			$buyer_content = '';
			// forming massiv of order
			$date_pay = $value->transactionDate ;
			$date_pay = date( 'm-d-Y H:i', $date_pay );
			if ( ! isset( $_REQUEST['s'] ) ) {
				$list_of_buyer[$i] = array(
					'id'         		=> $value->id,
					'status_trash'     	=> $the_buyer_status_trash,
					'buyerInfo' 		=> $buyerInfo,
					'paymentReason'		=> $paymentReason,
					'transactionAmount' => $transactionAmount,
					'referenceId'		=> $referenceId,
					'status'			=> $status,
					'paymentMethod' 	=> $paymentMethod
				);
				$i++;
				

			} else {
				$search_request = '/' . $_REQUEST['s'] . '/';
				if ( preg_match( $search_request, stripslashes( $buyerInfo ) ) ) {
					$list_of_buyer[$i] = array(
						'id'         		=> $value->id,
						'status_trash'     	=> $the_buyer_status_trash,
						'buyerInfo' 		=> $buyerInfo,
						'paymentReason'		=> $paymentReason,
						'transactionAmount' => $transactionAmount,
						'referenceId'		=> $referenceId,
						'status'			=> $status,
						'paymentMethod' 	=> $paymentMethod
					);
					$i++;
				}
			}
		}
		return $list_of_buyer;
	}
}

/*
** Function to get number of buyer 
*/
if ( ! function_exists( 'amznpmnts_number_of_buyer' ) ) {
	function amznpmnts_number_of_buyer() {
		global $wpdb;
		$prefix = $wpdb->prefix . 'amznpmnts_';
		$sql_query = "SELECT COUNT(`id`) FROM " . $prefix . "buyer_info ";
		if ( isset( $_REQUEST['buyer_status'] ) ) { // depending on request display different list of buyer
			if ( 'all' == $_REQUEST['buyer_status'] ) {
				$sql_query .= "WHERE " . $prefix . "buyer_info.status_id='1'";
			} elseif ( 'trash' == $_REQUEST['buyer_status'] ) {
				$sql_query .= "WHERE " . $prefix . "buyer_info.status_id='2'";
			}
		} else {
			$sql_query .= "WHERE " . $prefix . "buyer_info.status_id='1'";
		}
		$number_of_buyer = $wpdb->get_var( $sql_query );
		return $number_of_buyer;
	}
}


/*
** create class amznpmnts_Manager to display list of buyer 
*/
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Amznpmnts_Manager extends WP_List_Table {

	/*
	** Constructor of class 
	*/
	function __construct() {
		global $status, $page;
		parent::__construct( array(
			'singular'  => __( 'buyer', 'amazon-payments' ),
			'plural'    => __( 'buyers', 'amazon-payments' ),
			'ajax'      => true,
			)
		);
	}
	
	/*
	** Function to prepare data before display 
	*/
	function prepare_items() {
		global $buyer_status, $wpdb;
		$buyer_status = isset( $_REQUEST['buyer_status'] ) ? $_REQUEST['buyer_status'] : 'all';
		if ( ! in_array( $buyer_status, array( 'all', 'trash' ) ) )
			$buyer_status = 'all';
		$search		= ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : '';
		$columns	= $this->get_columns();
		$hidden		= array();
		$sortable 	= array();
		$total_items= intval( amznpmnts_number_of_buyer() );
		$per_page	= 5;
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->found_data = amznpmnts_get_list();
		$this->set_pagination_args( 
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
		$this->items = $this->found_data;
	}

	/*
	** Function to show order if no data found
	*/
	function no_items() {
		global $buyer_status;
		if ( 'trash' == $buyer_status ) {
			echo '<i>- ' . __( 'No order that was marked as Trash.', 'amazon-payments' ) . ' -<i>';
		} else {
			echo '<i>- ' . __( 'No order found.', 'amazon-payments' ) . ' -<i>';
		}
	}

	/*
	** Function to add column names 'amount'
	*/
	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'id':
			case 'status_trash':
			case 'buyerInfo':
			case 'paymentReason':
			case 'transactionAmount':
			case 'referenceId':
			case 'status':
			case 'paymentMethod':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ) ;
		}
	}


	/*
	** Function to add column titles 
	*/
	function get_columns(){
		$columns = array(
			'cb'					=> '<input type="checkbox" />',
			'id'					=> '',
			'status_trash'			=> '',
			'buyerInfo'				=> __( 'Buyer Info', 'amazon-payments' ),
			'paymentReason'			=> __( 'Name Product', 'amazon-payments' ),
			'transactionAmount'		=> __( 'Amount', 'amazon-payments' ),
			'referenceId'			=> __( 'Reference Id', 'amazon-payments' ),
			'status'				=> __( 'Status', 'amazon-payments' ),
			'paymentMethod'			=> __( 'Payment Method', 'amazon-payments' ),
		);
		return $columns;
	}
	/*
	** Function to add action links before and after list of order 
	*/
	function extra_tablenav( $which ) {
		global $buyer_status, $prefix, $wpdb;
		$status_links = array();
		$total_items = count( amznpmnts_get_list() );
		$status = array(
			'all'		=> __( 'All', 'amazon-payments' ),
			'trash'		=> __( 'Trash', 'amazon-payments' )
		);
		$prefix = $wpdb->prefix . 'amznpmnts_';
		$filters_count = $wpdb->get_results(
			"SELECT COUNT(`id`) AS `All`,
				( SELECT COUNT(`id`) FROM " . $prefix . "buyer_info WHERE " . $prefix . "buyer_info.status_id=2 ) AS `trash`
			FROM " . $prefix . "buyer_info WHERE " . $prefix . "buyer_info.status_id NOT IN (2)"
		);
		foreach( $filters_count as $value ) {
			$trash_count = $value->trash;
		} ?>
		<div class="amznpmnts-manager-filters">
			<?php foreach ( $status as $key => $value ) {
				$class = ( $key == $buyer_status ) ? ' class="current"' : '';
				echo ' <a href="?page=amznpmnts_manager&buyer_status=' . $key . '" ' . $class . '>' . $value;
				if ( 'all' != $key ) {
					echo ' <span class="count">( <span class="';
					if ( 'trash' == $key ) {
						echo 'trash-count">' . $trash_count;
					}
					echo '</span> )</span></a>';
				}
				if ( 'trash' != $key )
					echo ' | ';
			} ?>
		</div>
	<?php }

	/*
	** Function to add action links to drop down menu before and after table depending on status page
	*/
	function get_bulk_actions() {
		global $buyer_status;
		$action = array();
		if ( 'all' == $buyer_status ) {			
			$action['trash'] = __( 'Move Order Info to Trash', 'amazon-payments' );
		}
		if ( 'trash' == $buyer_status ) {
			$action['restore'] 	= __( 'Restore order information', 'amazon-payments' );
			$action['deletes'] 	= __( 'Delete order information', 'amazon-payments' );
		}
		return $action;
	}

	/*
	** Function to add action links to  order column depenting on status page
	*/
	function column_buyerInfo( $item ) {
		global $buyer_status;
		$action = array();
		if ( 'all' == $buyer_status ) {	
			$action['trash']	= sprintf( '<a href="?page=amznpmnts_manager&action=trash&buyer_id[]=%s">' . __( 'Trash', 'amazon-payments' ) . '</a>',$item['id'] );
		}
		if ( 'trash' == $buyer_status ) {
			$action['delete'] 	= sprintf( '<a href="?page=amznpmnts_manager&action=delete&buyer_id[]=%s">' . __( 'Delete', 'amazon-payments' ) . '</a>', $item['id'] );
			$action['restore'] = sprintf( '<a href="?page=amznpmnts_manager&action=restore&buyer_id[]=%s">' . __( 'Restore', 'amazon-payments' ) . '</a>', $item['id'] );
		}
  		return sprintf( '%1$s %2$s', $item['buyerInfo'], $this->row_actions( $action ) );
	}

	/*
	* Function to add column of checboxes 
	*/
	function column_cb( $item ) {
		return sprintf( '<input id="cb_%1s" type="checkbox" name="buyer_id[]" value="%2s" />', $item['id'], $item['id'] );
	}
}

/*
** Function to display pugin page
*/
if ( ! function_exists( 'amznpmnts_manager_page' ) ) {
	function amznpmnts_manager_page() {
		global $wpdb, $prefix, $amznpmnts_manager, $amznpmnts_done, $amznpmnts_option_defaults, $amznpmnts_error; 
		$amznpmnts_manager = new Amznpmnts_Manager();
		// set value of input type="hidden" when options is changed ?>
		<div id="main_amznpmnts_option">
			<div class="wrap">
				<div id="amznpmnts-tabs">
					<div id="amznpmnts-items-wrapper">
						<h2>
							<?php _e( "List of buyer", 'amazon-payments' ); ?>
							<?php if ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] )
								printf( '<span class="subtitle">' . sprintf( __( 'Search results for &#8220;%s&#8221;', 'amazon-payments' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ) . '</span>' );
							$amznpmnts_manager->prepare_items(); ?>
						</h2>
						<div class="clear"></div>
						<div class="updated" <?php if ( '' == $amznpmnts_done ) echo 'style="display: none;"'?>><p><?php echo $amznpmnts_done ?></p></div>
						<div class="error" <?php if ( '' == $amznpmnts_error ) echo 'style="display: none;"'?>><p><strong><?php __( 'WARNING: ', 'amazon-payments' ); ?></strong><?php echo $amznpmnts_error .  __( ' Please, try it later.', 'amazon-payments' ); ?></p></div>
						<div class="updated fade" style="display: none;"></div>			
						<form method="post">
							<?php $amznpmnts_manager->search_box( 'Search buyer', 'search_id' );
							$amznpmnts_manager->prepare_items();
							$amznpmnts_manager->display(); ?>
						</form>
					</div><!-- #amznpmnts-items-wrapper -->
				</div><!-- .amznpmnts-tabs -->
			</div><!-- .wrap -->
			<div class="clearfix-option"></div>
		</div>
	<?php }
}

/*
** Function to add stylesheets and scripts for admin bar 
*/
if ( ! function_exists ( 'amznpmnts_admin_head' ) ) {
	function amznpmnts_admin_head() {
		/* Call register settings function */
		$amznpmnts_pages = 'amznpmnts_manager';
		if ( 'amznpmnts_manager' == isset( $_REQUEST['page'] ) ){
			global $wp_version;
			if ( $wp_version < 3.8 )
				wp_enqueue_style( 'amznpmnts_stylesheet', plugins_url( '/css/style_wp_before_3.8.css', __FILE__ ) );	
			else
				wp_enqueue_style( 'amznpmnts_stylesheet', plugins_url( '/css/style.css', __FILE__ ) );
		}
		wp_enqueue_script( 'amznpmnts_script', plugins_url( 'js/script.js', __FILE__ ) ); ?>
	<?php }
}

/* 
** Function for delete delete options.
*/
if ( ! function_exists ( 'amznpmnts_delete_options' ) ) {
	function amznpmnts_delete_options() {
		global $wpdb, $prefix;
		$prefix = $wpdb->prefix . 'amznpmnts_';
		$sql = "DROP TABLE `" . $prefix . "buyer_info`, `" . $prefix . "amazon_sittings`;" ;
		$wpdb->query( $sql );
	}
}

/* Activate plugin */
register_activation_hook( __FILE__, 'amznpmnts_plugin_activate' );
/* add menu items in to dashboard menu */
add_action( 'admin_menu', 'amznpmnts_admin_menu' );
/* init hooks */
add_action( 'init', 'amznpmnts_init' );
add_action( 'admin_init', 'amznpmnts_admin_init' );
add_action( 'wp_head', 'amznpmnts_cunstruct_class' );
/*add pligin scripts and stylesheets*/
add_action( 'admin_enqueue_scripts', 'amznpmnts_admin_head' );
/*add pligin shortcode*/
add_shortcode( 'amazon_payments', 'amznpmnts_action' );
add_filter( 'widget_text', 'do_shortcode' );
/* add action link of plugin on "Plugins" page */
add_filter( 'plugin_action_links', 'amznpmnts_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'amznpmnts_register_plugin_links', 10, 2 );
/* uninstal hook */
register_uninstall_hook( __FILE__, 'amznpmnts_delete_options' );
