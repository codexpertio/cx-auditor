<?php
namespace Codexpert\CX_Auditor;

use Codexpert\CX_Auditor\Admin\Menu;
use Codexpert\CX_Auditor\Admin\Assets;
use Codexpert\CX_Auditor\Auditor;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	private static $instance = null;

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init(): void {
		load_plugin_textdomain( 'cx-auditor', false, dirname( CX_AUDITOR_BASENAME ) . '/languages' );

		( new Menu() )->register();
		( new Assets() )->register();

		add_action( 'wp_ajax_cx_auditor_run', [ Auditor::class, 'run_ajax' ] );
		add_action( 'admin_init', [ $this, 'handle_pdf_download' ] );
	}

	public function handle_pdf_download(): void {
		if ( isset( $_GET['action'] ) && 'cx_auditor_download_pdf' === $_GET['action'] ) {
			( new Menu() )->handle_pdf_download();
		}
	}
}