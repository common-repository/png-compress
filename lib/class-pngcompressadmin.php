<?php
/**
 * Png Compress
 *
 * @package    Png Compress
 * @subpackage PngCompressAdmin Management screen
	Copyright (c) 2020- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$pngcompressadmin = new PngCompressAdmin();

/** ==================================================
 * Management screen
 */
class PngCompressAdmin {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );
		add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_custom_wp_admin_style' ) );
	}

	/** ==================================================
	 * Add a "Settings" link to the plugins page
	 *
	 * @param array  $links  links array.
	 * @param string $file  file.
	 * @return array $links  links array.
	 * @since 1.00
	 */
	public function settings_link( $links, $file ) {

		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = 'png-compress/pngcompress.php';
		}
		if ( $file === $this_plugin ) {
			$links[] = '<a href="' . admin_url( 'upload.php?page=pngcompress-settings' ) . '">' . esc_html__( 'Settings' ) . '</a>';
		}
		return $links;
	}

	/** ==================================================
	 * Add Menu page
	 *
	 * @since 1.00
	 */
	public function plugin_menu() {

		add_media_page(
			__( 'PNG Compress', 'png-compress' ),
			__( 'PNG Compress', 'png-compress' ),
			'manage_options',
			'pngcompress-settings',
			array( $this, 'settings_page' )
		);
	}

	/** ==================================================
	 * Add Css and Script
	 *
	 * @since 1.00
	 */
	public function load_custom_wp_admin_style() {

		if ( $this->is_my_plugin_screen() ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'pngcompress-admin-js', plugin_dir_url( __DIR__ ) . 'js/jquery.pngcompress.admin.js', array( 'jquery' ), '1.0.0', false );
		}
	}

	/** ==================================================
	 * For only admin style
	 *
	 * @since 1.00
	 */
	private function is_my_plugin_screen() {

		$screen = get_current_screen();
		if ( is_object( $screen ) && 'media_page_pngcompress-settings' == $screen->id ) {
			return true;
		} else {
			return false;
		}
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function settings_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
		}

		$this->options_updated();
		$pngcompress_settings = get_option( 'pngcompress' );
		$scriptname = admin_url( 'upload.php?page=pngcompress-settings' );

		?>
		<div class="wrap">
		<h2>Png Compress</h2>

			<details>
			<summary><strong><?php esc_html_e( 'Various links of this plugin', 'png-compress' ); ?></strong></summary>
			<?php $this->credit(); ?>
			</details>

			<div class="wrap">
				<h2><?php esc_html_e( 'Settings' ); ?></h2>	

				<form method="post" action="<?php echo esc_url( $scriptname ); ?>">
				<?php wp_nonce_field( 'pgc_set', 'pngcompress_set' ); ?>

				<details style="margin-bottom: 5px;" open>
				<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php esc_html_e( 'Quality', 'png-compress' ); ?></strong></summary>

				<div style="margin: 5px; padding: 5px;">
					<div>
					<?php esc_html_e( 'Quality', 'png-compress' ); ?>
					<input type="range" id="quality_bar" style="vertical-align:middle;" name="tmp_jpg_quality" value="<?php echo esc_attr( $pngcompress_settings['tmp_jpg_quality'] ); ?>" min="0" max="100" step="1"><span id="quality_range"></span>
					</div>
					<div style="margin: 5px; padding: 5px;">
						<p class="description">
						<?php esc_html_e( 'JPEG Intermediate File Quality', 'png-compress' ); ?>
						</p>
					</div>
					<details style="margin-bottom: 5px;">
					<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php esc_html_e( 'Filter sample', 'png-compress' ); ?></strong></summary>
						<div style="margin: 5px; padding: 5px;">
							<code>add_filter( 'tmp_jpg_quality', function(){ return 50; }, 10, 1 );</code>
						</div>
					</details>
					<hr>
					<div>
					<?php esc_html_e( 'Compress', 'png-compress' ); ?>
					<input type="range" id="compress_bar" style="vertical-align:middle;" name="png_quality" value="<?php echo esc_attr( $pngcompress_settings['png_quality'] ); ?>" min="0" max="9" step="1"><span id="compress_range"></span>
					</div>
					<div style="margin: 5px; padding: 5px;">
						<p class="description">
						<?php esc_html_e( 'PNG Compression Level', 'png-compress' ); ?>
						</p>
					</div>
					<details style="margin-bottom: 5px;">
					<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php esc_html_e( 'Filter sample', 'png-compress' ); ?></strong></summary>
						<div style="margin: 5px; padding: 5px;">
							<code>add_filter( 'png_quality', function(){ return 9; }, 10, 1 );</code>
						</div>
					</details>
				</div>
				</details>

				<details style="margin-bottom: 5px;" open>
				<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php esc_html_e( 'Compressing an uploaded file', 'png-compress' ); ?></strong></summary>
				<div style="margin: 5px; padding: 5px;">
					<input type="checkbox" name="upload_file_compress" value="1" <?php checked( $pngcompress_settings['upload_file_compress'], true ); ?>><?php esc_html_e( 'Apply' ); ?>
					<div style="margin: 5px; padding: 5px;">
						<p class="description">
						<?php esc_html_e( 'If checked, compress the original uploaded file and the generated thumbnails. If unchecked, only the generated thumbnails will be compressed.', 'png-compress' ); ?>
						</p>
					</div>
				</div>
				</details>

				<details style="margin-bottom: 5px;" open>
				<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php esc_html_e( 'Note', 'png-compress' ); ?></strong></summary>
					<div style="margin: 5px; padding: 5px;">
						<?php
						$tinypng = '<a style="text-decoration: none;" href="https://tinypng.com/" target="_blank" rel="noopener noreferrer">TinyPNG</a>';
						?>
						<p class="description">
						<?php
						/* translators: Link for TinyPNG */
						echo wp_kses_post( sprintf( __( 'Note: This has no effect on files compressed with %1$s. Also, you can\'t expect to get the excellent compression and image quality that %1$s provides.', 'png-compress' ), $tinypng ) );
						?>
						</p>
					</div>
				</details>

				<?php submit_button( __( 'Save Changes' ), 'large', 'Manageset', false ); ?>
				</form>
			</div>

		</div>
		<?php
	}

	/** ==================================================
	 * Credit
	 *
	 * @since 1.00
	 */
	private function credit() {

		$plugin_name    = null;
		$plugin_ver_num = null;
		$plugin_path    = plugin_dir_path( __DIR__ );
		$plugin_dir     = untrailingslashit( wp_normalize_path( $plugin_path ) );
		$slugs          = explode( '/', $plugin_dir );
		$slug           = end( $slugs );
		$files          = scandir( $plugin_dir );
		foreach ( $files as $file ) {
			if ( '.' === $file || '..' === $file || is_dir( $plugin_path . $file ) ) {
				continue;
			} else {
				$exts = explode( '.', $file );
				$ext  = strtolower( end( $exts ) );
				if ( 'php' === $ext ) {
					$plugin_datas = get_file_data(
						$plugin_path . $file,
						array(
							'name'    => 'Plugin Name',
							'version' => 'Version',
						)
					);
					if ( array_key_exists( 'name', $plugin_datas ) && ! empty( $plugin_datas['name'] ) && array_key_exists( 'version', $plugin_datas ) && ! empty( $plugin_datas['version'] ) ) {
						$plugin_name    = $plugin_datas['name'];
						$plugin_ver_num = $plugin_datas['version'];
						break;
					}
				}
			}
		}
		$plugin_version = __( 'Version:' ) . ' ' . $plugin_ver_num;
		/* translators: FAQ Link & Slug */
		$faq       = sprintf( __( 'https://wordpress.org/plugins/%s/faq', 'png-compress' ), $slug );
		$support   = 'https://wordpress.org/support/plugin/' . $slug;
		$review    = 'https://wordpress.org/support/view/plugin-reviews/' . $slug;
		$translate = 'https://translate.wordpress.org/projects/wp-plugins/' . $slug;
		$facebook  = 'https://www.facebook.com/katsushikawamori/';
		$twitter   = 'https://twitter.com/dodesyo312';
		$youtube   = 'https://www.youtube.com/channel/UC5zTLeyROkvZm86OgNRcb_w';
		$donate    = __( 'https://shop.riverforest-wp.info/donate/', 'png-compress' );

		?>
		<span style="font-weight: bold;">
		<div>
		<?php echo esc_html( $plugin_version ); ?> | 
		<a style="text-decoration: none;" href="<?php echo esc_url( $faq ); ?>" target="_blank" rel="noopener noreferrer">FAQ</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $support ); ?>" target="_blank" rel="noopener noreferrer">Support Forums</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $review ); ?>" target="_blank" rel="noopener noreferrer">Reviews</a>
		</div>
		<div>
		<a style="text-decoration: none;" href="<?php echo esc_url( $translate ); ?>" target="_blank" rel="noopener noreferrer">
		<?php
		/* translators: Plugin translation link */
		echo esc_html( sprintf( __( 'Translations for %s' ), $plugin_name ) );
		?>
		</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-facebook"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-twitter"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $youtube ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-video-alt3"></span></a>
		</div>
		</span>

		<div style="width: 250px; height: 180px; margin: 5px; padding: 5px; border: #CCC 2px solid;">
		<h3><?php esc_html_e( 'Please make a donation if you like my work or would like to further the development of this plugin.', 'png-compress' ); ?></h3>
		<div style="text-align: right; margin: 5px; padding: 5px;"><span style="padding: 3px; color: #ffffff; background-color: #008000">Plugin Author</span> <span style="font-weight: bold;">Katsushi Kawamori</span></div>
		<button type="button" style="margin: 5px; padding: 5px;" onclick="window.open('<?php echo esc_url( $donate ); ?>')"><?php esc_html_e( 'Donate to this plugin &#187;' ); ?></button>
		</div>

		<?php
	}

	/** ==================================================
	 * Update wp_options table.
	 *
	 * @since 1.00
	 */
	private function options_updated() {

		if ( isset( $_POST['Manageset'] ) && ! empty( $_POST['Manageset'] ) ) {
			if ( check_admin_referer( 'pgc_set', 'pngcompress_set' ) ) {
				$pngcompress_settings = get_option( 'pngcompress' );
				if ( isset( $_POST['tmp_jpg_quality'] ) ) {
					$pngcompress_settings['tmp_jpg_quality'] = intval( $_POST['tmp_jpg_quality'] );
				}
				if ( isset( $_POST['png_quality'] ) ) {
					$pngcompress_settings['png_quality'] = intval( $_POST['png_quality'] );
				}
				if ( isset( $_POST['upload_file_compress'] ) && ! empty( $_POST['upload_file_compress'] ) ) {
					$pngcompress_settings['upload_file_compress'] = true;
				} else {
					$pngcompress_settings['upload_file_compress'] = false;
				}
				update_option( 'pngcompress', $pngcompress_settings );
				echo '<div class="notice notice-success is-dismissible"><ul><li>' . esc_html__( 'Settings' ) . ' --> ' . esc_html__( 'Settings saved.' ) . '</li></ul></div>';
			}
		}
	}

	/** ==================================================
	 * Settings register
	 *
	 * @since 1.00
	 */
	public function register_settings() {

		if ( ! get_option( 'pngcompress' ) ) {
			$pngcompress_tbl = array(
				'tmp_jpg_quality' => 50,
				'png_quality' => 9,
				'upload_file_compress' => true,
			);
			update_option( 'pngcompress', $pngcompress_tbl );
		}
	}
}


