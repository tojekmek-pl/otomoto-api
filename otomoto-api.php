<?php
set_time_limit(0);

/*
Plugin Name: Otomoto API
Description: Wtyczka umożliwiająca synchronizację asortymentu z Otomoto.
Version: 0.7
Author: Tojekmek
Author URI: https://tojekmek.pl
*/

include(plugin_dir_path(__FILE__) . 'plugin-update-checker/plugin-update-checker.php');
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/tojekmek-pl/otomoto-api',
	__FILE__,
	'otomoto-api'
);

// $myUpdateChecker->getVcsApi()->enableReleaseAssets();
require_once(ABSPATH . 'wp-admin/includes/image.php');
class PageTemplaterImport
{

	/**

	 * A reference to an instance of this class.

	 */

	private static $instance;



	/**

	 * The array of templates that this plugin tracks.

	 */

	protected $templates;



	/**

	 * Returns an instance of this class.

	 */

	public static function get_instance()
	{



		if (null == self::$instance) {

			self::$instance = new PageTemplaterImport();
		}



		return self::$instance;
	}



	/**

	 * Initializes the plugin by setting filters and administration functions.

	 */

	private function __construct()
	{



		$this->templates = array();





		// Add a filter to the attributes metabox to inject template into the cache.

		if (version_compare(floatval(get_bloginfo('version')), '4.7', '<')) {



			// 4.6 and older

			add_filter(

				'page_attributes_dropdown_pages_args',

				array($this, 'register_project_templates')

			);
		} else {



			// Add a filter to the wp 4.7 version attributes metabox

			add_filter(

				'theme_page_templates',
				array($this, 'add_new_template')

			);
		}



		// Add a filter to the save post to inject out template into the page cache

		add_filter(

			'wp_insert_post_data',

			array($this, 'register_project_templates')

		);







		// Add a filter to the template include to determine if the page has our

		// template assigned and return it's path

		add_filter(

			'template_include',

			array($this, 'view_project_template')

		);

		add_action('edit_form_top', 'add_custom_form');

		// if (isset($_POST['post_id'])) {
		// 	$post_id = $_POST['post_id'];

		// 	$otomoto_id = get_post_meta($_POST['post_id'], 'otomoto_id');

		// 	$long = get_field('samochod_lokalizacja', $post_id) == 'gliwice' ? "18.58702" : "19.06848";
		// 	$latit = get_field('samochod_lokalizacja', $post_id) == 'gliwice' ? "50.33793" : "50.22196";

		// 	$region_id = "6";
		// 	$city_id = get_field('samochod_lokalizacja', $post_id) == 'gliwice' ? "6091" : "7691";
		// 	$district_id = get_field('samochod_lokalizacja', $post_id) == 'gliwice' ? "181" : "229";

		// 	$municipality = ucfirst(get_field('samochod_lokalizacja', $post_id));

		// 	$paint_type = get_field('samochod_kolor_typ', $post_id);

		// 	$explodedMake = explode('|', get_field('samochod_marka', $post_id));

		// 	$make = $explodedMake[1];
		// 	$model = $explodedMake[2];
		// 	$version = $explodedMake[3] ?? '';

		// 	$city = [
		// 		'pl' => ucfirst(get_field('samochod_lokalizacja', $post_id)),
		// 		'en' => ucfirst(get_field('samochod_lokalizacja', $post_id))
		// 	];
		// 	$district = [
		// 		'pl' => ucfirst(get_field('samochod_lokalizacja', $post_id)),
		// 		'en' => ucfirst(get_field('samochod_lokalizacja', $post_id))
		// 	];

		// 	$params = [
		// 		"make" => $make,
		// 		"model" => $model,
		// 		"version" => $version,
		// 		"year" => get_field('samochod_rok', $post_id),
		// 		"mileage" => get_field('samochod_przebieg', $post_id),
		// 		"engine_capacity" => get_field('samochod_pojemnosc', $post_id),
		// 		"fuel_type" => get_field('samochod_paliwo', $post_id),
		// 		"engine_power" => get_field('samochod_moc', $post_id),
		// 		"gearbox" => get_field('samochod_skrzynia', $post_id),
		// 		"transmission" => get_field('samochod_naped', $post_id),
		// 		'no_accident' => get_field('samochod_bezkolizyjny', $post_id),
		// 		"door_count" => get_field('samochod_liczba_drzwi', $post_id),
		// 		"nr_seats" => get_field('samochod_liczba_miejsc', $post_id),
		// 		"color" => get_field('samochod_kolor', $post_id),
		// 		$paint_type => 1,
		// 		"vat_discount" => get_field('samochod_fvmarza', $post_id),
		// 		"financial_option" => get_field('samochod_finansowanie', $post_id),
		// 		"vat" => get_field('samochod_fv', $post_id),
		// 		"leasing_concession" => get_field('samochod_leasing', $post_id),
		// 		"date_registration"  => get_field('samochod_pierwsza_rejestracja', $post_id),
		// 		"price" => [
		// 			'price',
		// 			get_field('samochod_cena', $post_id),
		// 			'currency' => 'PLN',
		// 			'gross_net' => 'gross'
		// 		],
		// 		"country_origin" => "pl",
		// 	];

		// 	$phones = [];
		// 	foreach(get_field('samochod_telefony', $post_id) as $number){
		// 		$phones[] = reset($number);
		// 	}

		// 	$data = [
		// 		'title' => get_the_title($post_id),
		// 		'description' => get_field('samochod_opis', $post_id),
		// 		'city_id' => $city_id,
		// 		'district_id' => $district_id,
		// 		'new_used' => get_field('samochod_stan', $post_id),
		// 		'region_id' => $district_id,
		// 		'city' => $city,
		// 		'district' => $district,
		// 		'category_id' => "29",
		// 		'municipality' => $municipality,
		// 		'advertiser_type' => 'business',
		// 		'contact' => [
		// 			'person' => 'Przedsiębiorstwo Euro-Kas KIA ' . $municipality,
		// 			'phone_numbers' => $phones,
		// 		],
		// 		'coordinates' => [
		// 			'latitude' => $latit,
		// 			'longitude' => $long,
		// 			'radius' => 0,
		// 			'zoom_level' => 13,
		// 		],
		// 		'params' => $params,
		// 	];

		// 	$jsoned_data = json_encode($data, JSON_PRETTY_PRINT);

		// 	$oAuthUrl = 'https://www.otomoto.pl/api/open/oauth/token';

		// 	$authArgsArr = ['body' => [
		// 		'client_id' => get_option('api-key-otomoto-id'),
		// 		'client_secret' => get_option('api-key-otomoto'),
		// 		'grant_type' => 'password',
		// 		'username' => null,
		// 		'password' => null,
		// 	]];
	
		// 	$accounts = [
		// 		'used_cars' => ['username' => get_option('used-cars-login'), 'password' => get_option('used-cars-password')],
		// 		'katowice' => ['username' => get_option('katowice-login'), 'password' => get_option('katowice-password')],
		// 		'gliwice' => ['username' => get_option('gliwice-login'), 'password' => get_option('gliwice-password')],
		// 	];
	
		// 	foreach ($accounts as $accountName => $accountCredentials) {

		// 		if ($accountCredentials['username']  == get_field('samochod_typogloszenia', $post_id)) {
		// 			$authArgsArr['body']['username'] = $accountCredentials['username'];
		// 			$authArgsArr['body']['password'] = $accountCredentials['password'];
	
		// 			$response = wp_remote_post($oAuthUrl, $authArgsArr);
		// 			$responseBody = wp_remote_retrieve_body($response);
		// 			$decoded = json_decode($responseBody, true);
		// 			$token = $decoded['access_token'];
		// 		}
		// 	}

		// 	$args = array(
		// 		'headers' => array(
		// 		'Content-Type'   => 'application/json',
		// 		'Authorization' => 'Bearer ' . $token
		// 		),
		// 		'body'      => $jsoned_data,
		// 	);
		// 	$requestUrl = "https://www.otomoto.pl/api/open/account/adverts";


		// 	if($otomoto_id != "" && is_numeric($otomoto_id)){
		// 		$args['method'] = 'PUT';
		// 		$requestUrl .= $otomoto_id;
		// 	}else{
		// 		$args['method'] = 'POST';
		// 	}
			
		// 	$result =  wp_remote_request( $requestUrl, $args );
		// 	$otomotoResponse = wp_remote_retrieve_body($result);
		// 	update_field('otomoto_id', $otomotoResponse['id'], $post_id);

		// 	return true;
		// }


		function add_custom_form()
		{
			global $current_screen;

			if ('samochod' != $current_screen->post_type || $current_screen->action == 'add') {
				return;
			}
			$post_id = get_post()->ID;
			$post = <<<MLS
<div class="postbox-container" style="margin-top:15px; width: 100%">

<div class="postbox">

	<div class="inside">

		<div class="main">
			<form method="post" method="POST" name="otomoto-form" action="">
			<p><strong>Wyślij do otomoto:</strong></p>
			<input type="hidden" value="$post_id" name="post_id" />
			<button form='otomoto-form' name="upload-otomoto" id="upload-otomoto" class="button button-primary">Zaktualizuj <span style="padding-top:3px" class="dashicons dashicons-arrow-up-alt"></span></button>
			</form>

		</div>

	</div>

</div>

</div>
MLS;
			echo $post;
		}





		// Add your templates to this array.

		$this->templates = array(

			'page-insert.php' => 'Insert FB ADS',

		);
	}



	/**

	 * Adds our template to the page dropdown for v4.7+

	 *

	 */

	public function add_new_template($posts_templates)
	{

		$posts_templates = array_merge($posts_templates, $this->templates);

		return $posts_templates;
	}



	/**

	 * Adds our template to the pages cache in order to trick WordPress

	 * into thinking the template file exists where it doens't really exist.

	 */

	public function register_project_templates($atts)
	{



		// Create the key used for the themes cache

		$cache_key = 'page_templates-' . md5(get_theme_root() . '/' . get_stylesheet());



		// Retrieve the cache list.

		// If it doesn't exist, or it's empty prepare an array

		$templates = wp_get_theme()->get_page_templates();

		if (empty($templates)) {

			$templates = array();
		}



		// New cache, therefore remove the old one

		wp_cache_delete($cache_key, 'themes');



		// Now add our template to the list of templates by merging our templates

		// with the existing templates array from the cache.

		$templates = array_merge($templates, $this->templates);



		// Add the modified cache to allow WordPress to pick it up for listing

		// available templates

		wp_cache_add($cache_key, $templates, 'themes', 1800);



		return $atts;
	}



	/**

	 * Checks if the template is assigned to the page

	 */

	public function view_project_template($template)
	{

		// Return the search template if we're searching (instead of the template for the first result)

		if (is_search()) {

			return $template;
		}



		// Get global post

		global $post;



		// Return template if post is empty

		if (!$post) {

			return $template;
		}



		// Return default template if we don't have a custom one defined

		if (!isset($this->templates[get_post_meta(

			$post->ID,
			'_wp_page_template',
			true

		)])) {

			return $template;
		}



		// Allows filtering of file path

		$filepath = apply_filters('page_templater_plugin_dir_path', plugin_dir_path(__FILE__));



		$file =  $filepath . get_post_meta(

			$post->ID,
			'_wp_page_template',
			true

		);



		// Just to be safe, we check if the file exist first

		if (file_exists($file)) {

			return $file;
		} else {

			echo $file;
		}



		// Return template

		return $template;
	}
}

add_action('plugins_loaded', array('PageTemplaterImport', 'get_instance'));



// create custom plugin settings menu

add_action('admin_menu', 'import_plugin_create_menu');



function import_plugin_create_menu()
{



	//create new top-level menu

	add_menu_page('Otomoto API', 'Otomoto API', 'administrator', __FILE__, 'import_options_page', plugins_url('icon.png', __FILE__));



	//call register settings function

	add_action('admin_init', 'register_import_system_settings');
}





function register_import_system_settings()
{

	register_setting('import_system_option', 'api-key-otomoto');
	register_setting('import_system_option', 'api-key-otomoto-id');
	register_setting('import_system_option', 'used-cars-login');
	register_setting('import_system_option', 'used-cars-password');
	register_setting('import_system_option', 'gliwice-login');
	register_setting('import_system_option', 'gliwice-password');
	register_setting('import_system_option', 'katowice-login');
	register_setting('import_system_option', 'katowice-password');
}

function download_image_from_url($imageurl)
{

	$imageSize = getimagesize($imageurl)['mime'];
	$explodedUrl = explode('/', $imageSize);
	$imagetype = end($explodedUrl);

	$uniq_name = date('dmY') . '' . (int) microtime(true);
	$filename = $uniq_name . '.' . $imagetype;

	$uploaddir = wp_upload_dir();
	$uploadfile = $uploaddir['path'] . '/' . $filename;
	$contents = file_get_contents($imageurl);
	$savefile = fopen($uploadfile, 'w');
	fwrite($savefile, $contents);
	fclose($savefile);

	$wp_filetype = wp_check_filetype(basename($filename), null);
	$attachment = array(
		'post_mime_type' => $wp_filetype['type'],
		'post_title' => $filename,
		'post_content' => '',
		'post_status' => 'inherit'
	);

	$attach_id = wp_insert_attachment($attachment, $uploadfile);
	$imagenew = get_post($attach_id);
	$fullsizepath = get_attached_file($imagenew->ID);
	$attach_data = wp_generate_attachment_metadata($attach_id, $fullsizepath);
	wp_update_attachment_metadata($attach_id, $attach_data);

	return $attach_id;
}
function attach_image_to_post($attachment_id, $parent_post_id)
{
	$field = 'field_5ff36cf6cc425';

	$array = get_field($field, $parent_post_id, false);
	if (!is_array($array)) {
		$array = array();
	}

	$array[] = $attachment_id;
	update_field($field, $array, $parent_post_id);
}

function add_download_action()
{
	if (isset($_POST["download-otomoto"]) && get_option('api-key-otomoto') && get_option('api-key-otomoto-id')) {

		$oAuthUrl = 'https://www.otomoto.pl/api/open/oauth/token';

		$authArgsArr = ['body' => [
			'client_id' => get_option('api-key-otomoto-id'),
			'client_secret' => get_option('api-key-otomoto'),
			'grant_type' => 'password',
			'username' => null,
			'password' => null,
		]];

		$accounts = [
			'used_cars' => ['username' => get_option('used-cars-login'), 'password' => get_option('used-cars-password')],
			'katowice' => ['username' => get_option('katowice-login'), 'password' => get_option('katowice-password')],
			'gliwice' => ['username' => get_option('gliwice-login'), 'password' => get_option('gliwice-password')],
		];

		$tokens = [];

		foreach ($accounts as $accountName => $accountCredentials) {
			if ($accountCredentials['username'] && $accountCredentials['password']) {

				$authArgsArr['body']['username'] = $accountCredentials['username'];
				$authArgsArr['body']['password'] = $accountCredentials['password'];

				$response = wp_remote_post($oAuthUrl, $authArgsArr);
				$responseBody = wp_remote_retrieve_body($response);

				$decoded = json_decode($responseBody, true);
				$token = $decoded['access_token'];

				$tokens[$accountCredentials['username']] = $token;
			}
		}

		if (count($tokens) > 0) {
			get_all_adverts($tokens);
		}
	}
}

add_action('init', 'add_download_action');


function get_all_adverts($tokens)
{
	$url = 'https://www.otomoto.pl/api/open/account/adverts';
	$authArgsArr = ['headers' => [
		'Authorization' => ''
	]];

	foreach ($tokens as $username => $token) {
		$authArgsArr['headers']['Authorization'] = 'Bearer ' . $token;
		$response = wp_remote_get($url, $authArgsArr);
		$responseBody = wp_remote_retrieve_body($response);

		$decodedResponse = json_decode($responseBody, true);

		$cars = $decodedResponse['results'];

		foreach ($cars as $car) {
			if ($car['status'] == 'active') {
				process_custom_post($car, $username);
			}
		}
	}
}

function add_phone_numbers($phones, $post_id)
{

	foreach ($phones as $phone) {
		add_row('samochod_telefony', ['samochod_telefony_telefon' => $phone], $post_id);
	}
}

function process_custom_post($car, $username)
{

	global $wpdb;

	$custom_post = array();
	$custom_post['post_type'] = 'samochod';
	$custom_post['post_status'] = 'publish';
	$custom_post['post_title'] = $car['params']['make'] . ' ' .  $car['params']['model'] . ' ' . $car['params']['year'];
	$post_id = wp_insert_post($custom_post);

	$make = isset($car['params']['make']) ? '|' . $car['params']['make'] : '';
	$model =  isset($car['params']['model']) ? '|' . $car['params']['model'] : '';
	$version = isset($car['params']['version']) ? '|' . $car['params']['version'] : '';

	// Prepare and insert the custom post meta
	$meta_keys = array();
	$meta_keys['otomoto_id'] = $car['id'] ?? '';
	$meta_keys['samochod_stan'] = $car['new_used'] ?? 'used';
	$meta_keys['samochod_otomoto_tytul'] = $car['title'] ?? '';
	$meta_keys['samochod_typogloszenia'] = $username;
	$meta_keys['samochod_cena'] = $car['params']['price'][1] ?? 0;
	$meta_keys['samochod_marka'] = '29' . $make . $model . $version;
	$meta_keys['samochod_model'] = $car['params']['model'];
	$meta_keys['samochod_rok'] = $car['params']['year'] ?? '';
	$meta_keys['samochod_przebieg'] = $car['params']['mileage'] ?? '';
	$meta_keys['samochod_pojemnosc'] = $car['params']['engine_capacity'] ?? '';
	$meta_keys['samochod_paliwo'] = $car['params']['fuel_type'] ?? '';
	$meta_keys['samochod_moc'] = $car['params']['engine_power'] ?? '';
	$meta_keys['samochod_skrzynia'] = $car['params']['gearbox'] ?? '';
	$meta_keys['samochod_naped'] = $car['params']['transmission'] ?? '';
	$meta_keys['samochod_kolor'] = $car['params']['color'] ?? '';
	$meta_keys['samochod_vin'] = $car['params']['vin'] ?? '';
	$meta_keys['samochod_liczba_drzwi'] = $car['params']['door_count'] ?? '';
	$meta_keys['samochod_liczba_miejsc'] = $car['params']['nr_seats'] ?? '';
	$meta_keys['samochod_pierwsza_rejestracja'] = $car['params']['date_registration'] ?? '';
	$meta_keys['samochod_finansowanie'] = $car['params']['financial_option'] ?? '';

	$meta_keys['samochod_fv'] = $car['params']['vat'] ?? '';
	$meta_keys['samochod_fvmarza'] = $car['params']['vat_discount'] ?? '';

	$meta_keys['samochod_leasing'] = $car['params']['leasing_concession'] ?? '';
	$meta_keys['samochod_bezkolizyjny'] = $car['params']['no_accident'] ?? '';
	$meta_keys['samochod_opis'] = $car['description'] ?? '';

	$meta_keys['samochod_lokalizacja'] = strtolower($car['city']['pl']);

	if ($car['params']['metallic'] == "1") {
		$meta_keys['samochod_kolor_typ'] = "metallic";
	} else if ($car['params']['pearl'] == "1") {
		$meta_keys['samochod_kolor_typ'] = "pearl";
	} else if ($car['params']['matt'] == "1") {
		$meta_keys['samochod_kolor_typ'] = "matt";
	} else {
		$meta_keys['samochod_kolor_typ'] = "acrylic";
	}

	$custom_fields = array();
	$place_holders = array();

	$query_string = "INSERT INTO $wpdb->postmeta ( post_id, meta_key, meta_value) VALUES ";
	foreach ($meta_keys as $key => $value) {
		array_push($custom_fields, $post_id, $key, $value);
		$place_holders[] = "('%d', '%s', '%s')";
	}
	$query_string .= implode(', ', $place_holders);
	$wpdb->query($wpdb->prepare("$query_string ", $custom_fields));

	$phones = $car['contact']['phone_numbers'] ?? [];
	if (count($phones)) {
		add_phone_numbers($phones, $post_id);
	}

	$image_ids = [];
	if (isset($car['photos'])) {

		foreach ($car['photos'] as $photo) {
			if (count($photo) > 0) {
				if (isset($photo['1080x720'])) {
					$image_ids[] = download_image_from_url($photo['1080x720']);
				} else {
					$image_ids[] = download_image_from_url(reset($photo));
				}
			}
		}

		if (count($image_ids)) {
			set_post_thumbnail($post_id, $image_ids[0]);
		}

		foreach ($image_ids as $attachement_id) {
			attach_image_to_post($attachement_id, $post_id);
		}
	}

	return true;
}

function import_options_page()
{
?>

	<div class="wrap">

		<h1>Otomoto API</h1>

		<div id="col-container">

			<div id="col-right" class="postbox-container">

				<div class="col-wrap">

					<h2>Ustawienia</h2>

					<div class="form-wrap">

						<form method="post" action="options.php">

							<?php settings_fields('import_system_option'); ?>

							<?php do_settings_sections('import_system_option'); ?>

							<div class="form-field">

								<label>Klucz API Otomoto</label>

								<input type="password" style="margin-bottom:10px" placeholder="Klucz" name="api-key-otomoto" value="<?php echo esc_attr(get_option('api-key-otomoto')); ?>" />
								<input type="text" placeholder="ID Klucza" name="api-key-otomoto-id" value="<?php echo esc_attr(get_option('api-key-otomoto-id')); ?>" />
								<hr>
								<label>Samochody używane</label>

								<input style="margin-bottom:10px" type="text" placeholder="Login" name="used-cars-login" value="<?php echo esc_attr(get_option('used-cars-login')); ?>" />
								<input type="password" placeholder="Hasło" name="used-cars-password" value="<?php echo esc_attr(get_option('used-cars-password')); ?>" />
								<hr>
								<label>Samochody od ręki Gliwice</label>

								<input style="margin-bottom:10px" type="text" placeholder="Login" name="gliwice-login" value="<?php echo esc_attr(get_option('gliwice-login')); ?>" />
								<input type="password" placeholder="Hasło" name="gliwice-password" value="<?php echo esc_attr(get_option('gliwice-password')); ?>" />

								<hr>
								<label>Hasło: Samochody od ręki Katowice</label>
								<input style="margin-bottom:10px" type="text" placeholder="Login" name="katowice-login" value="<?php echo esc_attr(get_option('katowice-login')); ?>" />
								<input type="password" placeholder="Hasło" name="katowice-password" value="<?php echo esc_attr(get_option('katowice-password')); ?>" />

							</div>

							<?php submit_button(); ?>

						</form>

						<h2>Akcje</h2>

						<form method="post" method="POST" action="">
							<button name="download-otomoto" id="download-otomoto" class="button">
								Pobierz asortyment <span style="padding-top:3px" class="dashicons dashicons-arrow-down-alt"></span>
							</button>
						</form>


					</div>

				</div>

			</div>
			<div id="col-left">
				<div class="col-wrap">
					<h2>Instrukcja</h3>

						<div class="postbox-container" style="width: 100%">

							<div class="postbox">

								<div class="inside">

									<div class="main">

										<p><strong>O wtyczce:</strong><br>

											Wtyczka umożliwa <strong>synchronizację asortymentu</strong> z platformą Otmototo.</p>

										<ol>

											<li>Dodaj klucz API uzyskany w panelu administracyjnym Otomoto oraz dane logowania do kont</li>

											<li>Kliknij przycisk <b>"Zaktualizuj asortyment"</b>, aby uzupełnić i zaktualizować oferty dostępne na Otomoto.</li>

											<li>Kliknij przycisk <b>"Pobierz asortyment"</b>, aby zaktualizować oraz pobrać brakujące zamówienia z Otomoto.</li>

										</ol>

										<div style="text-align:right">

											<img src="<?php echo plugins_url('icon.png', __FILE__) ?>" alt="logo">

										</div>

									</div>

								</div>

							</div>

						</div>

				</div>

			</div>
		</div>

	</div>

<?php }
