<?php
/**
 * Png Compress
 *
 * @package    Png Compress
 * @subpackage PngCompress Main function
/*  Copyright (c) 2020- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
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

$pngcompress = new PngCompress();

/** ==================================================
 * Class Main function
 *
 * @since 1.00
 */
class PngCompress {

	/** ==================================================
	 * Dir
	 *
	 * @var $upload_dir DIR.
	 */
	private $upload_dir;

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		$wp_uploads = wp_upload_dir();
		$relation_path_true = strpos( $wp_uploads['baseurl'], '../' );
		if ( $relation_path_true > 0 ) {
			$upload_dir = wp_normalize_path( realpath( $wp_uploads['basedir'] ) );
		} else {
			$upload_dir = wp_normalize_path( $wp_uploads['basedir'] );
		}
		$this->upload_dir = untrailingslashit( $upload_dir );

		add_filter( 'wp_generate_attachment_metadata', array( $this, 'generate_png' ), 10, 2 );
	}

	/** ==================================================
	 * Png generate
	 *
	 * @param array $metadata  metadata.
	 * @param int   $attachment_id  ID.
	 * @return array $metadata  metadata.
	 * @since 1.00
	 */
	public function generate_png( $metadata, $attachment_id ) {

		$mime_type = get_post_mime_type( $attachment_id );
		if ( 'image/png' === $mime_type ) {
			$tmp_jpg_quality = apply_filters( 'tmp_jpg_quality', $tmp_jpg_quality = null );
			$png_quality = apply_filters( 'png_quality', $png_quality = null );
			$pngcompress_settings = get_option( 'pngcompress' );
			$qualities = $this->png_quality_func( $tmp_jpg_quality, $png_quality, $pngcompress_settings );
			$file_tmp = $this->change_ext( $metadata['file'] );
			foreach ( (array) $metadata['sizes'] as $key => $value ) {
				$file_thumb     = $value['file'];
				$file_thumb_tmp = $this->change_ext( $file_thumb );
				if ( '.' === dirname( $file_tmp ) ) {
					$dir_name_url  = '/';
					$dir_name_path = wp_normalize_path( '/' );
				} else {
					$dir_name_url = '/' . dirname( $file_tmp ) . '/';
					$dir_name_path = wp_normalize_path( $dir_name_url );
				}
				$path = $this->upload_dir . $dir_name_path;
				$ret  = $this->create_png( $path . $file_thumb, $path . $file_thumb_tmp, $qualities );
			}
			if ( array_key_exists( 'original_image', $metadata ) && ! empty( $metadata['original_image'] ) ) {
				$org_img_file = wp_get_original_image_path( $attachment_id, false );
				$org_png_file = $this->change_ext( $org_img_file );
				$ret = $this->create_png( $org_img_file, $org_png_file, $qualities );
			}
			if ( $pngcompress_settings['upload_file_compress'] ) {
				$ret = $this->create_png( $this->upload_dir . '/' . $metadata['file'], $this->upload_dir . '/' . $file_tmp, $qualities );
			}
		}

		return $metadata;
	}

	/** ==================================================
	 * PNG and JPEG Quality
	 *
	 * @param int   $tmp_jpg_quality  JPEG quality.
	 * @param int   $png_quality  PNG quality.
	 * @param array $pngcompress_settings  settings.
	 * @since 1.00
	 */
	private function png_quality_func( $tmp_jpg_quality, $png_quality, $pngcompress_settings ) {

		$qualities['tmp_jpg_quality'] = $tmp_jpg_quality;
		$qualities['png_quality'] = $png_quality;

		if ( is_null( $tmp_jpg_quality ) || 0 > $tmp_jpg_quality || 100 < $tmp_jpg_quality ) {
			$qualities['tmp_jpg_quality'] = $pngcompress_settings['tmp_jpg_quality'];
		}
		if ( is_null( $png_quality ) || 0 > $png_quality || 9 < $png_quality ) {
			$qualities['png_quality'] = $pngcompress_settings['png_quality'];
		}

		return $qualities;
	}

	/** ==================================================
	 * Png create
	 *
	 * @param string $filename  input filename for original png.
	 * @param string $filename_tmp_png  output filename for compressed png.
	 * @param array  $qualities  JPEG and PNG quality.
	 * @return bool $ret create bool.
	 * @since 1.00
	 */
	private function create_png( $filename, $filename_tmp_png, $qualities ) {

		if ( ! file_exists( $filename ) ) {
			return false;
		}
		if ( file_exists( $filename_tmp_png ) ) {
			return false;
		}

		$filename_tmp_jpg = $filename_tmp_png . 'jpg';

		@set_time_limit( 60 );

		$ret = false;

		/* png to jpg */
		$src = imagecreatefrompng( $filename );
		$img = imagecreatetruecolor( imagesx( $src ), imagesy( $src ) );
		imagealphablending( $img, false );
		imagesavealpha( $img, true );

		imagecopy( $img, $src, 0, 0, 0, 0, imagesx( $src ), imagesy( $src ) );
		imagedestroy( $src );
		$ret = imagejpeg( $img, $filename_tmp_jpg, $qualities['tmp_jpg_quality'] );
		imagedestroy( $img );

		$src = imagecreatefromjpeg( $filename_tmp_jpg );
		$img = imagecreatetruecolor( imagesx( $src ), imagesy( $src ) );
		imagefill( $img, 0, 0, imagecolorallocate( $img, 255, 255, 255 ) );
		imagealphablending( $img, true );

		imagecopy( $img, $src, 0, 0, 0, 0, imagesx( $src ), imagesy( $src ) );
		imagedestroy( $src );
		$ret = imagepng( $img, $filename_tmp_png, $qualities['png_quality'] );
		imagedestroy( $img );

		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
		$wp_filesystem = new WP_Filesystem_Direct( false );

		if ( $ret ) {
			$ret = wp_delete_file( $filename );
			$ret = wp_delete_file( $filename_tmp_jpg );
			$ret = $wp_filesystem->move( $filename_tmp_png, $filename );
		}

		return $ret;
	}

	/** ==================================================
	 * Change ext
	 *
	 * @param string $before_file_name  before_file_name.
	 * @return string $after_file_name  after_file_name.
	 * @since 1.00
	 */
	private function change_ext( $before_file_name ) {

		$exts            = explode( '.', $before_file_name );
		$before_ext      = '.' . end( $exts );
		$after_file_name = str_replace( $before_ext, '.tmppng', $before_file_name );

		return $after_file_name;
	}
}
