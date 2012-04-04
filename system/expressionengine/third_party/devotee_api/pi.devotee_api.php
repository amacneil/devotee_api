<?php

$plugin_info = array(
						'pi_name'			=> 'Devotee API',
						'pi_version'		=> '1.0',
						'pi_author'			=> 'Isaac Raway (Airways)',
						'pi_author_url'		=> 'http://metasushi.com',
						'pi_description'	=> 'Provides access to the Devot:ee JSON API',
						'pi_usage'			=> Devotee_api::usage());

class Devotee_api
{
	function __construct()
	{
		$this->EE =& get_instance();
	}

	function orders()
	{
		$request = array();
		$request['api_key'] = $this->EE->TMPL->fetch_param('api_key');
		$request['secret_key'] = $this->EE->TMPL->fetch_param('secret_key');

		// start and date in YYYY-MM-DD format
		$request['start_date'] = $this->EE->TMPL->fetch_param('start_date');
		$request['end_date'] = $this->EE->TMPL->fetch_param('end_date');

		$ch = curl_init('https://devot-ee.com/api/orders');
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request));

		// stupid bad behaviour script on Devot:ee
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.142 Safari/535.19');

		$response = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);

		if ($error)
		{
			return $error;
		}

		$orders = json_decode($response, TRUE);

		if (empty($orders['items'][0]))
		{
			$orders = array(array('no_results' => TRUE));
		}
		else
		{
			$orders = $orders['items'][0];
		}

		return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $orders);
	}

	static function usage()
	{
		ob_start();?>
See http://devot-ee.com/sell/orders-api/ for a complete list of fields available for each order.

Parmeters:

	api_key - your Devot:ee API key
	secret_key - your Devot:ee Secret key
	start_date - format YYYY-MM-DD
	end_date - format YYYY-MM-DD

Variables:

	purchase_id - Unique id for this purchase
	item order_id - Order ID
	order_date - Order date in Unix Timestamp format.
	product_id - Product ID
	quantity - Product Quantity
	price - Product Price
	license_key - UUID license key
	license_req - (y|n) license key is required in configuration
	title - Product Title
	customer_email - Customer's Email
	customer_phone - Customer's Phone
	customer_billing_fname - Customer First Name
	customer_billing_lname - Customer Last Name
	customer_company - Company
	customer_billing_address1 - Address 1
	customer_billing_address2 - Address 2
	customer_billing_city - City
	customer_billing_state - State
	customer_billing_zip - Zip Code
	customer_billing_country - Country code

<table border="1">
<tr>
	<th>Product</th>
	<th>Purchase ID</th>
	<th>Customer E-mail</th>
	<th>License</th>
</tr>
{exp:devotee_api:orders username="{devotee_username}" password="{devotee_password}" start_date="2011-01-01" end_date="2011-06-01"}
	{if no_results == 'yes'}
		<tr>
			<td colspan="4" align="center">No results!</td>
		</tr>
	{if:else}
		<tr>
			<td>{title}</td>
			<td>{purchase_id}</td>
			<td>{customer_email}</td>
			<td>{license_key}</td>
		</tr>
	{/if}
{/exp:devotee_api:orders}
</table>
		<?
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	} // function usage
} // class devotee_api










