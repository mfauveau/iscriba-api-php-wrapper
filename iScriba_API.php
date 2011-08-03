<?php
/**
 * copyright (c) 2011 Matthieu Fauveau - matthieufauveau.com
 *
 * iScriba API PHP Wrapper is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * iScriba API PHP Wrapper is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with iScriba API PHP Wrapper.
 * If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * iScriba API PHP Wrapper
 *
 * PHP Wrapper Library for iScriba API
 *
 * @author Matthieu Fauveau
 * @version 1.0
 */

/**
 * iScriba API Exception Object
 */
class iScriba_API_Exception extends Exception {
}

/**
 * iScriba API Result Object
 */
class iScriba_API_Result
{

	protected $_code;
	protected $_data;
	protected $_headers;

	/**
	 * Constructor
	 *
	 * @param string  $code    response code
	 * @param array   $data    response data
	 * @param array   $headers array of response headers
	 */
	public function __construct($code = NULL, $data = NULL, $headers = NULL)
	{
		$this->_code  = $code;
		$this->_data  = $data;
		$this->_headers = $headers;
	}

	/**
	 * Magic method to return non public properties
	 *
	 * @see     get
	 * @param mixed   $property
	 *
	 * @return  mixed
	 */
	public function __get($property)
	{
		return $this->get($property);
	}

	/**
	 * Get
	 *
	 * @param string  $property
	 *
	 * @return mixed
	 */
	public function get($property = '')
	{
		switch ($property) {
		case 'code':
			return $this->_code;
			break;

		case 'data':
			return $this->_data;
			break;
		case 'headers':
			return $this->_headers;
			break;
		default:
			if ($this->_headers != null and array_key_exists($property, $this->_headers)) {
				return $this->_headers[$property];
			} else {
				throw new iScriba_API_Exception(sprintf('Unknown property %s::%s', get_class($this), $property));
			}
			break;
		}
	}

	/**
	 * Magic method to set non public properties
	 *
	 * @see    set
	 * @param mixed   $property
	 * @param mixed   $value
	 *
	 * @return void
	 */
	public function __set($property, $value)
	{
		$this->set($property, $value);
	}

	/**
	 * Set
	 *
	 * @param string  $property
	 * @param string  $value
	 *
	 * @return iScriba_API_Object
	 */
	public function set($property = '', $value = '')
	{
		switch ($property) {
		case 'code':
			$this->_code = $value;
			break;

		case 'data':
			$this->_data = $value;
			break;

		case 'headers':
			$this->_headers = $value;
			break;

		default:
			throw new iScriba_API_Exception(sprintf('Unknown property %s::%s', get_class($this), $property));
			break;
		}
	}

	public function is_success()
	{
		if ('2' == substr($this->_code, 0, 1)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

/**
 * iScriba_API_Object defines the base class utilized by all iScriba Objects
 */
abstract class iScriba_API_Object
{

	protected $_values = array();

	/**
	 * Magic method to return non public properties
	 *
	 * @see     get
	 * @param mixed   $property
	 *
	 * @return  mixed
	 */
	public function __get($property)
	{
		return $this->get($property);
	}

	/**
	 * Get
	 *
	 * @param string  $property
	 *
	 * @return mixed
	 */
	public function get($property = '')
	{
		$value = NULL;

		if (array_key_exists($property, $this->_values)) {
			return $this->_values[$property];
		} else {
			return NULL;
		}
	}

	/**
	 * Magic method to set non public properties
	 *
	 * @see    set
	 * @param mixed   $property
	 * @param mixed   $value
	 *
	 * @return void
	 */
	public function __set($property, $value)
	{
		$this->set($property, $value);
	}

	/**
	 * Set
	 *
	 * @param string  $property
	 * @param string  $value
	 *
	 * @return iScriba_API_Object
	 */
	public function set($property = '', $value = '')
	{
		if (is_array($property)) {
			foreach ($property as $key => $val) {
				$this->_values[$key] = $val;
			}
		}
		else {
			$this->_values[$property] = $value;
		}

		return $this;
	}
}

/**
 * iScriba API Fields Object
 */
class iScriba_API_Fields extends iScriba_API_Object {

	/**
	 * To POST
	 *
	 * Convert iScriba_API_Object into post fields
	 *
	 * @return string
	 */
	public function to_post()
	{
		return $this->_build_post_fields($this->_values);
	}

	private function _build_post_fields($data, $key = '')
	{
		$return = array();

		foreach ((array) $data as $k => $v) {
			if (( ! empty($key)) or ($key === 0)) {
				$k = $key .'['. rawurlencode($k) .']';
			}

			if (is_array($v) or is_object($v)) {
				array_push($return, $this->_build_post_fields($v, $k));
			} else {
				array_push($return, $k .'='. rawurlencode($v));
			}
		}

		return implode('&', $return);
	}
}

/**
 * iScriba API Arguments Object
 */
class iScriba_API_Arguments extends iScriba_API_Object {

	/**
	 * To URI
	 *
	 * Convert iScriba_API_Object into an URI string
	 *
	 * @return string
	 */
	public function to_uri()
	{
		return $this->_build_uri_string($this->_values);
	}

	private function _build_uri_string($data)
	{
		$return = array();

		foreach ((array) $data as $k => $v) {
			if (is_array($v) or is_object($v)) {
				array_push($return, $k .'/'. rawurlencode(implode(':', $v)));
			} else {
				array_push($return, $k .'/'. rawurlencode($v));
			}
		}

		return implode('/', $return);
	}
}

/**
 * iScriba API defines the methods available to the API
 *
 * <code>
 * require('iScriba_API.php');
 *
 * $api = new iScriba_API($username, $password, $subdomain);
 * </code>
 */
class iScriba_API
{

	protected $_username;
	protected $_password;
	protected $_subdomain;

	protected $_headers;
	protected $_useragent = 'PHP Wrapper Library for iScriba API/1.0';
	protected $_format    = 'xml';

	private $_supported_formats = array(
		'xml'   => 'application/xml',
		'rawxml'  => 'application/xml',
		'json'   => 'application/json',
		'jsonp'  => 'application/javascript',
		'serialize' => 'application/vnd.php.serialized',
		'php'   => 'application/x-httpd-php'
	);

	/**
	 * Constructor
	 *
	 * @param string  $username
	 * @param string  $password
	 * @param string  $subdomain
	 *
	 * @return void
	 */
	public function __construct($username = '', $password = '', $subdomain = '')
	{
		$this->_username  = $username;
		$this->_password  = $password;
		$this->_subdomain  = $subdomain;
	}

	/**
	 * Set user agent
	 *
	 * @param string  $useragent
	 *
	 * @return iScriba_API
	 */
	public function set_useragent($useragent = '')
	{
		$this->_useragent = $useragent;

		return $this;
	}

	/**
	 * Set format
	 *
	 * Set the format in which the response will be sent
	 *
	 * @param string  $format
	 *
	 * @return iScriba_API
	 */
	public function set_format($format = '')
	{
		if (array_key_exists($format, $this->_supported_formats)) {
			$this->_format = $format;
		}

		return $this;
	}

	/**
	 * Reset headers
	 *
	 * @return void
	 */
	protected function reset_headers()
	{
		$this->_headers = array();
	}

	/**
	 * Parse headers
	 *
	 * Parse cURL Header text
	 *
	 * @param cURL-Handle $ch
	 * @param string  $header
	 *
	 * @return int
	 */
	protected function parse_headers($ch, $header)
	{
		$pos = strpos($header, ':');
		$key = substr($header, 0, $pos);
		$value = trim(substr($header, $pos + 1));
		if ($key == 'Location') {
			$this->_headers[$key] = trim(substr($value, strrpos($value, '/') + 1));
		} else {
			$this->_headers[$key] = $value;
		}
		return strlen($header);
	}

	/**
	 * Request
	 *
	 * @param string  $http_verb
	 * @param string  $uri
	 * @param string  $data
	 *
	 * @return iScriba_API_Result
	 */
	public function request($http_verb = '', $uri = '', $data = '')
	{
		$this->reset_headers();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://'. $this->_subdomain .'.iscriba.com/api/'. $uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'User-Agent: '. $this->_useragent,
				'Accept: '. $this->_supported_formats[$this->_format],
				'Authorization: Basic ('. base64_encode($this->_username .':'. $this->_password) .')'
			));
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this, 'parse_headers'));

		switch ($http_verb) {
		case 'GET':
			break;

		case 'POST':
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			break;

		case 'PUT':
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			break;

		case 'DELETE':
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
			break;
		}

		$data = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if (curl_errno($ch)) {
			throw new iScriba_API_Exception(curl_error($ch));
		}

		return new iScriba_API_Result($code, $data, $this->_headers);
	}

	// --------------------------------------------------------------------
	// Companies Resources
	// --------------------------------------------------------------------

	/**
	 * Get companies
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $result = $api->get_companies();
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @return iScriba_API_Result
	 */
	public function get_companies()
	{
		return $this->request('GET', 'people/companies');
	}

	/**
	 * Get company
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $company_id = 12345;
	 *
	 * $result = $api->get_company($company_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $company_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_company($company_id = 0)
	{
		return $this->request('GET', 'people/company/id/'. $company_id);
	}

	/**
	 * Create company
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $company = new iScriba_API_Fields();
	 * $company->set('field', 'value');
	 *
	 * $result = $api->create_company($company);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Fields $company
	 *
	 * @return iScriba_API_Result
	 */
	public function create_company(iScriba_API_Fields $company)
	{
		return $this->request('POST', 'people/company', $company->to_post());
	}

	/**
	 * Update company
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $company_id = 12345;
	 *
	 * $company = new iScriba_API_Fields();
	 * $company->set('field', 'value');
	 *
	 * $result = $api->update_company($company_id, $company);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $company_id
	 * @param iScriba_API_Fields $company
	 *
	 * @return iScriba_API_Result
	 */
	public function update_company($company_id = 0, iScriba_API_Fields $company)
	{
		return $this->request('PUT', 'people/company/id/'. $company_id, $company->to_post());
	}

	/**
	 * Delete company
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $company_id = 12345;
	 *
	 * $result = $api->delete_company($company_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $company_id
	 *
	 * @return iScriba_API_Result
	 */
	public function delete_company($company_id = 0)
	{
		return $this->request('DELETE', 'people/company/id/'. $company_id);
	}

	// --------------------------------------------------------------------
	// Users Resources
	// --------------------------------------------------------------------

	/**
	 * Get users
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $result = $api->get_users();
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $company_id (optional)
	 *
	 * @return iScriba_API_Result
	 */
	public function get_users($company_id = 0)
	{
		$uri = 'people/users';

		if ($company_id != 0) {
			$uri .= '/id/'. $company_id;
		}

		return $this->request('GET', $uri);
	}

	/**
	 * Get user
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $user_id = 12345;
	 *
	 * $result = $api->get_user($user_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $user_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_user($user_id = 0)
	{
		return $this->request('GET', 'people/user/id/'. $user_id);
	}

	/**
	 * Create user
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $user = new iScriba_API_Fields();
	 * $user->set('field', 'value');
	 *
	 * $result = $api->create_user($user);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Fields $user
	 *
	 * @return iScriba_API_Result
	 */
	public function create_user(iScriba_API_Fields $user)
	{
		return $this->request('POST', '/people/user', $user->to_post());
	}

	/**
	 * Update user
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $user_id = 12345;
	 *
	 * $user = new iScriba_API_Fields();
	 * $user->set('field', 'value');
	 *
	 * $result = $api->create_user($user_id, $user);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $user_id
	 * @param iScriba_API_Fields $user
	 *
	 * @return iScriba_API_Result
	 */
	public function update_user($user_id = 0, iScriba_API_Fields $user)
	{
		return $this->request('PUT', 'people/user/id/'. $user_id, $user->to_post());
	}

	/**
	 * Delete user
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $user_id = 12345;
	 *
	 * $result = $api->delete_user($user_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $user_id
	 *
	 * @return iScriba_API_Result
	 */
	public function delete_user($user_id = 0)
	{
		return $this->request('DELETE', 'people/user/id/'. $user_id);
	}

	// --------------------------------------------------------------------
	// Inventory Resources
	// --------------------------------------------------------------------

	/**
	 * Get inventory items
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $filter = new iScriba_API_Arguments();
	 * $filter->set('argument', 'value'); // optional
	 *
	 * $result = $api->get_inventory_items($filter);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Arguments $filter
	 *
	 * @return iScriba_API_Result
	 */
	public function get_inventory_items(iScriba_API_Arguments $filter)
	{
		return $this->request('GET', 'inventory/items/'. $filter->to_uri());
	}

	/**
	 * Get inventory item
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $item_id = 12345;
	 *
	 * $result = $api->get_inventory_item($item_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $item_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_inventory_item($item_id = 0)
	{
		return $this->request('GET', 'inventory/item/id/'. $item_id);
	}

	/**
	 * Create inventory item
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $item = new iScriba_API_Fields();
	 * $item->set('field', 'value');
	 *
	 * $result = $api->create_inventory_item($item);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Fields $item
	 *
	 * @return iScriba_API_Result
	 */
	public function create_inventory_item(iScriba_API_Fields $item)
	{
		return $this->request('POST', 'inventory/item', $item->to_post());
	}

	/**
	 * Update inventory item
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $item_id = 12345;
	 *
	 * $item = new iScriba_API_Fields();
	 * $item->set('field', 'value');
	 *
	 * $result = $api->update_inventory_item($item_id, $item);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $item_id
	 * @param iScriba_API_Fields $item
	 *
	 * @return iScriba_API_Result
	 */
	public function update_inventory_item($item_id = 0, iScriba_API_Fields $item)
	{
		return $this->request('PUT', 'inventory/item/id/'. $item_id, $item->to_post());
	}

	/**
	 * Delete inventory item
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $item_id = 12345;
	 *
	 * $result = $api->delete_inventory_item($item_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $item_id
	 *
	 * @return iScriba_API_Result
	 */
	public function delete_inventory_item($item_id = 0)
	{
		return $this->request('DELETE', 'inventory/item/id/'. $item_id);
	}

	// --------------------------------------------------------------------
	// Packinglists Resources
	// --------------------------------------------------------------------

	/**
	 * Get packinglists
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $filter = new iScriba_API_Arguments();
	 * $filter->set('argument', 'value'); // optional
	 *
	 * $result = $api->get_packinglists($filter);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Arguments $filter
	 *
	 * @return iScriba_API_Result
	 */
	public function get_packinglists(iScriba_API_Arguments $filter)
	{
		return $this->request('GET', 'packinglists/packinglists/'. $filter->to_uri());
	}

	/**
	 * Get packinglist
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $packinglist_id = 12345;
	 *
	 * $result = $api->get_packinglist($packinglist_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $packinglist_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_packinglist($packinglist_id = 0)
	{
		return $this->request('GET', 'packinglists/packinglist/id/'. $packinglist_id);
	}

	/**
	 * Get packinglist PDF
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $packinglist_id = 12345;
	 *
	 * $result = $api->get_packinglist_pdf($packinglist_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $packinglist_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_packinglist_pdf($packinglist_id = 0)
	{
		return $this->request('GET', 'packinglists/packinglist/id/'. $packinglist_id .'/format/pdf');
	}

	/**
	 * Create packinglist
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $packinglist = new iScriba_API_Fields();
	 * $packinglist->set(array(
	 *   'date' => '2011-01-29',
	 *  'client_id' => 12345,
	 *  'address' => array(
	 *    'address1' => 'Address line 1',
	 *   'address2' => 'Address line 2'
	 *    // and so on...
	 *  ),
	 *  lines => array(
	 *   array(
	 *    'kind' => 4,
	 *    'description' => 'Some product description'
	 *   ),
	 *   array(
	 *    'kind' => 1,
	 *    'description' => 'Some service description'
	 *   )
	 *   // and so on...
	 *  )
	 * );
	 *
	 * $result = $api->create_packinglist($packinglist);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Fields $packinglist
	 *
	 * @return iScriba_API_Result
	 */
	public function create_packinglist(iScriba_API_Fields $packinglist)
	{
		return $this->request('POST', 'packinglists/packinglist', $packinglist->to_post());
	}

	/**
	 * Update packinglist
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $packinglist_id = 12345;
	 *
	 * $packinglist = new iScriba_API_Fields();
	 * $packinglist->set(array(
	 *  'address' => array(
	 *    'address1' => 'Address line 1',
	 *   'address2' => 'Address line 2'
	 *    // and so on...
	 *  ),
	 *  lines => array(
	 *   array(
	 *    'kind' => 4,
	 *    'description' => 'Some product description',
	 *    'unit_price' => '99.99'
	 *   ),
	 *   array(
	 *    'kind' => 1,
	 *    'description' => 'Some service description',
	 *    'unit_price' => '99.99'
	 *   )
	 *   // and so on...
	 *  )
	 * );
	 *
	 * $result = $api->update_packinglist($packinglist_id, $packinglist);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $packinglist_id
	 * @param iScriba_API_Fields $packinglist
	 *
	 * @return iScriba_API_Result
	 */
	public function update_packinglist($packinglist_id = 0, iScriba_API_Fields $packinglist)
	{
		return $this->request('PUT', 'packinglists/packinglist/id/'. $packinglist_id, $packinglist->to_post());
	}

	/**
	 * Update packinglist status
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $packinglist_id = 12345;
	 *
	 * $packinglist = new iScriba_API_Fields();
	 * $packinglist->set('status', 'delivered');
	 *
	 * $result = $api->update_packinglist_status($packinglist_id, $packinglist);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $packinglist_id
	 * @param iScriba_API_Fields $packinglist
	 *
	 * @return iScriba_API_Result
	 */
	public function update_packinglist_status($packinglist_id = 0, iScriba_API_Fields $packinglist)
	{
		return $this->request('PUT', 'packinglists/packinglist_status/id/'. $packinglist_id, $packinglist->to_post());
	}

	/**
	 * Delete packinglist
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $packinglist_id = 12345;
	 *
	 * $result = $api->delete_packinglist($packinglist_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $packinglist_id
	 *
	 * @return iScriba_API_Result
	 */
	public function delete_packinglist($packinglist_id = 0)
	{
		return $this->request('DELETE', 'packinglists/packinglist/id/'. $packinglist_id);
	}

	/**
	 * Get packinglist related comments
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $packinglist_id = 12345;
	 *
	 * $result = $api->get_packinglist_related_comments($packinglist_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $packinglist_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_packinglist_related_comments($packinglist_id = 0)
	{
		return $this->request('GET', 'packinglists/packinglist_related_comments/id/'. $packinglist_id);
	}

	/**
	 * Create packinglist related comment
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $comment = new iScriba_API_Fields();
	 * $comment->set(array(
	 *  'packinglist_id' => 12345,
	 *   'content' => 'Comment content',
	 *  'is_public' => 0
	 * );
	 *
	 * $result = $api->create_packinglist_related_comment($comment);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Fields $comment
	 *
	 * @return iScriba_API_Result
	 */
	public function create_packinglist_related_comment(iScriba_API_Fields $packinglist)
	{
		return $this->request('POST', 'packinglists/packinglist_related_comment', $packinglist->to_post());
	}

	/**
	 * Delete packinglist related comment
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $comment_id = 12345;
	 *
	 * $result = $api->delete_packinglist_related_comment($comment_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $comment_id
	 *
	 * @return iScriba_API_Result
	 */
	public function delete_packinglist_related_comment($comment_id = 0)
	{
		return $this->request('DELETE', 'packinglists/packinglist_related_comment/id/'. $comment_id);
	}

	/**
	 * Get packinglist related documents
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $packinglist_id = 12345;
	 *
	 * $result = $api->get_packinglist_related_documents($packinglist_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $packinglist_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_packinglist_related_documents($packinglist_id = 0)
	{
		return $this->request('GET', 'packinglists/packinglist_related_documents/id/'. $packinglist_id);
	}

	/**
	 * Get packinglist related tags
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $packinglist_id = 12345;
	 *
	 * $result = $api->get_packinglist_related_tags($packinglist_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $packinglist_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_packinglist_related_tags($packinglist_id = 0)
	{
		return $this->request('GET', 'packinglists/packinglist_related_tags/id/'. $packinglist_id);
	}

	// --------------------------------------------------------------------
	// Payments Resources
	// --------------------------------------------------------------------

	/**
	 * Get payments
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $filter = new iScriba_API_Arguments();
	 * $filter->set('argument', 'value'); // optional
	 *
	 * $result = $api->get_payments($filter);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Arguments $filter
	 *
	 * @return iScriba_API_Result
	 */
	public function get_payments(iScriba_API_Arguments $filter)
	{
		return $this->request('GET', 'payments/payments/'. $filter->to_uri());
	}

	/**
	 * Get payment
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $payment_id = 12345;
	 *
	 * $result = $api->get_payment($payment_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $payment_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_payment($payment_id = 0)
	{
		return $this->request('GET', 'payments/payment/id/'. $payment_id);
	}

	/**
	 * Receive payment
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $payment = new iScriba_API_Fields();
	 * $payment->set(array(
	 *   'amount' => '99.99',
	 *  'client_id' => 12345,
	 *  'currency' => 'EUR',
	 *  'invoices' => array(
	 *   12345 => 99.99 // $invoice_id => $amount_applied
	 *    // you can apply payment to several invoices by populating the array
	 *  )
	 * );
	 *
	 * $result = $api->receive_payment($payment);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Fields $payment
	 *
	 * @return iScriba_API_Result
	 */
	public function receive_payment(iScriba_API_Fields $payment)
	{
		return $this->request('POST', 'payments/payment', $payment->to_post());
	}

	/**
	 * Delete payment
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $payment_id = 12345;
	 *
	 * $result = $api->delete_payment($payment_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $payment_id
	 *
	 * @return iScriba_API_Result
	 */
	public function delete_payment($payment_id = 0)
	{
		return $this->request('DELETE', 'payments/payment/id/'. $payment_id);
	}

	/**
	 * Use credit
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $payment = new iScriba_API_Fields();
	 * $payment->set(array(
	 *   'payment_id' => 12345,
	 *  'client_id' => 12345,
	 *  'currency' => 'EUR',
	 *  'invoices' => array(
	 *   12345 => 99.99 // $invoice_id => $amount_applied
	 *    // you can apply credit to several invoices by populating the array
	 *  )
	 * );
	 *
	 * $result = $api->use_credit($credit);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Fields $credit
	 *
	 * @return iScriba_API_Result
	 */
	public function use_credit(iScriba_API_Fields $credit)
	{
		return $this->request('POST', 'payments/credit', $credit->to_post());
	}

	// --------------------------------------------------------------------
	// Invoices Resources
	// --------------------------------------------------------------------

	/**
	 * Get invoices
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $filter = new iScriba_API_Arguments();
	 * $filter->set('argument', 'value'); // optional
	 *
	 * $result = $api->get_invoices($filter);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Arguments $filter
	 *
	 * @return iScriba_API_Result
	 */
	public function get_invoices(iScriba_API_Arguments $filter)
	{
		return $this->request('GET', 'invoices/invoices/'. $filter->to_uri());
	}

	/**
	 * Get invoice
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $invoice_id = 12345;
	 *
	 * $result = $api->get_invoice($invoice_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $invoice_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_invoice($invoice_id = 0)
	{
		return $this->request('GET', 'invoices/invoice/id/'. $invoice_id);
	}

	/**
	 * Get invoice PDF
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $invoice_id = 12345;
	 *
	 * $result = $api->get_invoice_pdf($invoice_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $invoice_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_invoice_pdf($invoice_id = 0)
	{
		return $this->request('GET', 'invoices/invoice/id/'. $invoice_id .'/format/pdf');
	}

	/**
	 * Create invoice
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $invoice = new iScriba_API_Fields();
	 * $invoice->set(array(
	 *   'date' => '2011-01-29',
	 *  'client_id' => 12345,
	 *  'address' => array(
	 *    'address1' => 'Address line 1',
	 *   'address2' => 'Address line 2'
	 *    // and so on...
	 *  ),
	 *  lines => array(
	 *   array(
	 *    'kind' => 4,
	 *    'description' => 'Some product description',
	 *    'unit_price' => '99.99'
	 *   ),
	 *   array(
	 *    'kind' => 1,
	 *    'description' => 'Some service description',
	 *    'unit_price' => '99.99'
	 *   )
	 *   // and so on...
	 *  )
	 * );
	 *
	 * $result = $api->create_invoice($invoice);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Fields $invoice
	 *
	 * @return iScriba_API_Result
	 */
	public function create_invoice(iScriba_API_Fields $invoice)
	{
		return $this->request('POST', 'invoices/invoice', $invoice->to_post());
	}

	/**
	 * Update invoice
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $invoice_id = 12345;
	 *
	 * $invoice = new iScriba_API_Fields();
	 * $invoice->set(array(
	 *  'address' => array(
	 *    'address1' => 'Address line 1',
	 *   'address2' => 'Address line 2'
	 *    // and so on...
	 *  ),
	 *  lines => array(
	 *   array(
	 *    'kind' => 4,
	 *    'description' => 'Some product description',
	 *    'unit_price' => '99.99'
	 *   ),
	 *   array(
	 *    'kind' => 1,
	 *    'description' => 'Some service description',
	 *    'unit_price' => '99.99'
	 *   )
	 *   // and so on...
	 *  )
	 * );
	 *
	 * $result = $api->update_invoice($invoice_id, $invoice);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $invoice_id
	 * @param iScriba_API_Fields $invoice
	 *
	 * @return iScriba_API_Result
	 */
	public function update_invoice($invoice_id = 0, iScriba_API_Fields $invoice)
	{
		return $this->request('PUT', 'invoices/invoice/id/'. $invoice_id, $invoice->to_post());
	}

	/**
	 * Update invoice status
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $invoice_id = 12345;
	 *
	 * $invoice = new iScriba_API_Fields();
	 * $invoice->set('status', 'sent');
	 *
	 * $result = $api->update_invoice_status($invoice_id, $invoice);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $invoice_id
	 * @param iScriba_API_Fields $invoice
	 *
	 * @return iScriba_API_Result
	 */
	public function update_invoice_status($invoice_id = 0, iScriba_API_Fields $invoice)
	{
		return $this->request('PUT', 'invoices/invoice_status/id/'. $invoice_id, $invoice->to_post());
	}

	/**
	 * Delete invoice
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $invoice_id = 12345;
	 *
	 * $result = $api->delete_invoice($invoice_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $invoice_id
	 *
	 * @return iScriba_API_Result
	 */
	public function delete_invoice($invoice_id = 0)
	{
		return $this->request('DELETE', 'invoices/invoice/id/'. $invoice_id);
	}

	/**
	 * Get invoice related comments
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $invoice_id = 12345;
	 *
	 * $result = $api->get_invoice_related_comments($invoice_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $invoice_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_invoice_related_comments($invoice_id = 0)
	{
		return $this->request('GET', 'invoices/invoice_related_comments/id/'. $invoice_id);
	}

	/**
	 * Create invoice related comment
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $comment = new iScriba_API_Fields();
	 * $comment->set(array(
	 *  'invoice_id' => 12345,
	 *   'content' => 'Comment content',
	 *  'is_public' => 0
	 * );
	 *
	 * $result = $api->create_invoice_related_comment($comment);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Fields $comment
	 *
	 * @return iScriba_API_Result
	 */
	public function create_invoice_related_comment(iScriba_API_Fields $invoice)
	{
		return $this->request('POST', 'invoices/invoice_related_comment', $invoice->to_post());
	}

	/**
	 * Delete invoice related comment
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $comment_id = 12345;
	 *
	 * $result = $api->delete_invoice_related_comment($comment_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $comment_id
	 *
	 * @return iScriba_API_Result
	 */
	public function delete_invoice_related_comment($comment_id = 0)
	{
		return $this->request('DELETE', 'invoices/invoice_related_comment/id/'. $comment_id);
	}

	/**
	 * Get invoice related documents
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $invoice_id = 12345;
	 *
	 * $result = $api->get_invoice_related_documents($invoice_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $invoice_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_invoice_related_documents($invoice_id = 0)
	{
		return $this->request('GET', 'invoices/invoice_related_documents/id/'. $invoice_id);
	}

	/**
	 * Get invoice related tags
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $invoice_id = 12345;
	 *
	 * $result = $api->get_invoice_related_tags($invoice_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $invoice_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_invoice_related_tags($invoice_id = 0)
	{
		return $this->request('GET', 'invoices/invoice_related_tags/id/'. $invoice_id);
	}

	/**
	 * Create invoice refund
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $invoice = new iScriba_API_Fields();
	 * $invoice->set(array(
	 *  'invoice_id' => 12345,
	 *  'refund_lines' => array(
	 *   array('line_id' => 12345)
	 *   )
	 * );
	 *
	 * $result = $api->create_invoice_refund($invoice_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Fields $invoice
	 *
	 * @return iScriba_API_Result
	 */
	public function create_invoice_refund(iScriba_API_Fields $invoice)
	{
		return $this->request('POST', 'invoices/refund', $invoice->to_post());
	}

	/**
	 * Delete invoice related payment
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $invoice_id = 12345;
	 * $payment_id = 12345;
	 *
	 * $result = $api->delete_invoice_related_payment($invoice_id, $payment_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $invoice_id
	 * @param int     $payment_id
	 *
	 * @return iScriba_API_Result
	 */
	public function delete_invoice_related_payment($invoice_id = 0, $payment_id = 0)
	{
		return $this->request('DELETE', 'invoices/invoice_related_payment/invoice_id/'. $invoice_id .'/payment_id/'. $payment_id);
	}

	// --------------------------------------------------------------------
	// Purchaseorders Resources
	// --------------------------------------------------------------------

	/**
	 * Get purchaseorders
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $filter = new iScriba_API_Arguments();
	 * $filter->set('argument', 'value'); // optional
	 *
	 * $result = $api->get_purchaseorders($filter);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Arguments $filter
	 *
	 * @return iScriba_API_Result
	 */
	public function get_purchaseorders(iScriba_API_Arguments $filter)
	{
		return $this->request('GET', 'purchaseorders/purchaseorders/'. $filter->to_uri());
	}

	/**
	 * Get purchaseorder
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $purchaseorder_id = 12345;
	 *
	 * $result = $api->get_purchaseorder($purchaseorder_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $purchaseorder_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_purchaseorder($purchaseorder_id = 0)
	{
		return $this->request('GET', 'purchaseorders/purchaseorder/id/'. $purchaseorder_id);
	}

	/**
	 * Get purchaseorder PDF
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $purchaseorder_id = 12345;
	 *
	 * $result = $api->get_purchaseorder_pdf($purchaseorder_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $purchaseorder_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_purchaseorder_pdf($purchaseorder_id = 0)
	{
		return $this->request('GET', 'purchaseorders/purchaseorder/id/'. $purchaseorder_id .'/format/pdf');
	}

	/**
	 * Update purchaseorder status
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $purchaseorder_id = 12345;
	 *
	 * $purchaseorder = new iScriba_API_Fields();
	 * $purchaseorder->set('status', 'signed');
	 *
	 * $result = $api->update_purchaseorder_status($purchaseorder_id, $purchaseorder);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $purchaseorder_id
	 * @param iScriba_API_Fields $purchaseorder
	 *
	 * @return iScriba_API_Result
	 */
	public function update_purchaseorder_status($purchaseorder_id = 0, iScriba_API_Fields $purchaseorder)
	{
		return $this->request('PUT', 'purchaseorders/purchaseorder_status/id/'. $purchaseorder_id, $purchaseorder->to_post());
	}

	/**
	 * Get purchaseorder related documents
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $purchaseorder_id = 12345;
	 *
	 * $result = $api->get_purchaseorder_related_documents($purchaseorder_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $purchaseorder_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_purchaseorder_related_documents($purchaseorder_id = 0)
	{
		return $this->request('GET', 'purchaseorders/purchaseorder_related_documents/id/'. $purchaseorder_id);
	}

	/**
	 * Get purchaseorder related tags
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $purchaseorder_id = 12345;
	 *
	 * $result = $api->get_purchaseorder_related_tags($purchaseorder_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $purchaseorder_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_purchaseorder_related_tags($purchaseorder_id = 0)
	{
		return $this->request('GET', 'purchaseorders/purchaseorder_related_tags/id/'. $purchaseorder_id);
	}

	// --------------------------------------------------------------------
	// Estimates Resources
	// --------------------------------------------------------------------

	/**
	 * Get estimates
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $filter = new iScriba_API_Arguments();
	 * $filter->set('argument', 'value'); // optional
	 *
	 * $result = $api->get_estimates($filter);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Arguments $filter
	 *
	 * @return iScriba_API_Result
	 */
	public function get_estimates(iScriba_API_Arguments $filter)
	{
		return $this->request('GET', 'estimates/estimates/'. $filter->to_uri());
	}

	/**
	 * Get estimate
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $estimate_id = 12345;
	 *
	 * $result = $api->get_estimate($estimate_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $estimate_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_estimate($estimate_id = 0)
	{
		return $this->request('GET', 'estimates/estimate/id/'. $estimate_id);
	}

	/**
	 * Get estimate PDF
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $estimate_id = 12345;
	 *
	 * $result = $api->get_estimate_pdf($estimate_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $estimate_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_estimate_pdf($estimate_id = 0)
	{
		return $this->request('GET', 'estimates/estimate/id/'. $estimate_id .'/format/pdf');
	}

	/**
	 * Create estimate
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $estimate = new iScriba_API_Fields();
	 * $estimate->set(array(
	 *   'date' => '2011-01-29',
	 *  'client_id' => 12345,
	 *  'address' => array(
	 *    'address1' => 'Address line 1',
	 *   'address2' => 'Address line 2'
	 *    // and so on...
	 *  ),
	 *  lines => array(
	 *   array(
	 *    'kind' => 4,
	 *    'description' => 'Some product description',
	 *    'unit_price' => '99.99'
	 *   ),
	 *   array(
	 *    'kind' => 1,
	 *    'description' => 'Some service description',
	 *    'unit_price' => '99.99'
	 *   )
	 *   // and so on...
	 *  )
	 * );
	 *
	 * $result = $api->create_estimate($estimate);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Fields $estimate
	 *
	 * @return iScriba_API_Result
	 */
	public function create_estimate(iScriba_API_Fields $estimate)
	{
		return $this->request('POST', 'estimates/estimate', $estimate->to_post());
	}

	/**
	 * Update estimate
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $estimate_id = 12345;
	 *
	 * $estimate = new iScriba_API_Fields();
	 * $estimate->set(array(
	 *  'address' => array(
	 *    'address1' => 'Address line 1',
	 *   'address2' => 'Address line 2'
	 *    // and so on...
	 *  ),
	 *  lines => array(
	 *   array(
	 *    'kind' => 4,
	 *    'description' => 'Some product description',
	 *    'unit_price' => '99.99'
	 *   ),
	 *   array(
	 *    'kind' => 1,
	 *    'description' => 'Some service description',
	 *    'unit_price' => '99.99'
	 *   )
	 *   // and so on...
	 *  )
	 * );
	 *
	 * $result = $api->update_estimate($estimate_id, $estimate);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $estimate_id
	 * @param iScriba_API_Fields $estimate
	 *
	 * @return iScriba_API_Result
	 */
	public function update_estimate($estimate_id = 0, iScriba_API_Fields $estimate)
	{
		return $this->request('PUT', 'estimates/estimate/id/'. $estimate_id, $estimate->to_post());
	}

	/**
	 * Update estimate status
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $estimate_id = 12345;
	 *
	 * $estimate = new iScriba_API_Fields();
	 * $estimate->set('status', 'signed');
	 *
	 * $result = $api->update_estimate_status($estimate_id, $estimate);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $estimate_id
	 * @param iScriba_API_Fields $estimate
	 *
	 * @return iScriba_API_Result
	 */
	public function update_estimate_status($estimate_id = 0, iScriba_API_Fields $estimate)
	{
		return $this->request('PUT', 'estimates/estimate_status/id/'. $estimate_id, $estimate->to_post());
	}

	/**
	 * Delete estimate
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $estimate_id = 12345;
	 *
	 * $result = $api->delete_estimate($estimate_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $estimate_id
	 *
	 * @return iScriba_API_Result
	 */
	public function delete_estimate($estimate_id = 0)
	{
		return $this->request('DELETE', 'estimates/estimate/id/'. $estimate_id);
	}

	/**
	 * Get estimate related comments
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $estimate_id = 12345;
	 *
	 * $result = $api->get_estimate_related_comments($estimate_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $estimate_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_estimate_related_comments($estimate_id = 0)
	{
		return $this->request('GET', 'estimates/estimate_related_comments/id/'. $estimate_id);
	}

	/**
	 * Create estimate related comment
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $comment = new iScriba_API_Fields();
	 * $comment->set(array(
	 *  'estimate_id' => 12345,
	 *   'content' => 'Comment content',
	 *  'is_public' => 0
	 * );
	 *
	 * $result = $api->create_estimate_related_comment($comment);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Fields $comment
	 *
	 * @return iScriba_API_Result
	 */
	public function create_estimate_related_comment(iScriba_API_Fields $estimate)
	{
		return $this->request('POST', 'estimates/estimate_related_comment', $estimate->to_post());
	}

	/**
	 * Delete estimate related comment
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $comment_id = 12345;
	 *
	 * $result = $api->delete_estimate_related_comment($comment_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $comment_id
	 *
	 * @return iScriba_API_Result
	 */
	public function delete_estimate_related_comment($comment_id = 0)
	{
		return $this->request('DELETE', 'estimates/estimate_related_comment/id/'. $comment_id);
	}

	/**
	 * Get estimate related documents
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $estimate_id = 12345;
	 *
	 * $result = $api->get_estimate_related_documents($estimate_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $estimate_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_estimate_related_documents($estimate_id = 0)
	{
		return $this->request('GET', 'estimates/estimate_related_documents/id/'. $estimate_id);
	}

	/**
	 * Get estimate related tags
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $estimate_id = 12345;
	 *
	 * $result = $api->get_estimate_related_tags($estimate_id);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param int     $estimate_id
	 *
	 * @return iScriba_API_Result
	 */
	public function get_estimate_related_tags($estimate_id = 0)
	{
		return $this->request('GET', 'estimates/estimate_related_tags/id/'. $estimate_id);
	}

	/**
	 * Estimate to invoice
	 *
	 * <code>
	 * $api = new iScriba_API($username, $password, $subdomain);
	 *
	 * $estimate = new iScriba_API_Fields();
	 * $estimate->set(array(
	 *  'estimate_id' => 12345,
	 *  'date' => '2011-01-29',
	 *  'notes' => 'Some notes'
	 * ));
	 *
	 * $result = $api->estimate_to_invoice($estimate);
	 * if ($result->is_success()) {
	 *    // additional logic
	 * }
	 * </code>
	 *
	 * @param iScriba_API_Fields $estimate
	 *
	 * @return iScriba_API_Result
	 */
	public function estimate_to_invoice(iScriba_API_Fields $estimate)
	{
		return $this->request('POST', 'estimates/estimate_to_invoice', $estimate->to_post());
	}
}
