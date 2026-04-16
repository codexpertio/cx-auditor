<?php
namespace Codexpert\CX_Auditor\Admin;

defined( 'ABSPATH' ) || exit;

class Menu {

	public const SLUG = 'cx-auditor';

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
	}

	public function add_menu(): void {
		add_menu_page(
			__( 'CX Auditor', 'cx-auditor' ),
			__( 'CX Auditor', 'cx-auditor' ),
			'manage_options',
			self::SLUG,
			[ $this, 'render' ],
			'dashicons-chart-bar',
			81
		);
	}

	public function render(): void {
		$view = CX_AUDITOR_PATH . 'src/Admin/views/dashboard.php';
		if ( file_exists( $view ) ) {
			include $view;
		}
	}

	public function handle_pdf_download(): void {
		error_log("PDF handler called");

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden.', 'cx-auditor' ), 403 );
		}
		error_log("User authorized");

		$nonce_check = check_admin_referer( 'cx_auditor_pdf' );
		error_log("Nonce check: " . ($nonce_check ? 'passed' : 'failed'));

		$site_url   = isset( $_GET['site_url'] ) ? esc_url_raw( wp_unslash( $_GET['site_url'] ) ) : '';
		$overall    = isset( $_GET['score'] ) ? (int) $_GET['score'] : 0;
		$generated  = isset( $_GET['generated'] ) ? sanitize_text_field( wp_unslash( $_GET['generated'] ) ) : '';
		$modules_enc = isset( $_GET['modules'] ) ? wp_unslash( $_GET['modules'] ) : '';

		error_log("PDF download: site_url=$site_url, modules_enc length=" . strlen($modules_enc));

		$modules = [];
		if ( $modules_enc ) {
			$raw = base64_decode( $modules_enc );
			error_log("Raw decoded length: " . strlen($raw));
			$decoded = mb_convert_encoding( $raw, 'UTF-8', 'UTF-8' );
			$modules = json_decode( $decoded, true ) ?: [];
			error_log("Modules decoded, count: " . count($modules));
		}

		if ( empty( $site_url ) || empty( $modules ) ) {
			wp_die( esc_html__( 'Invalid report data.', 'cx-auditor' ) . " site_url=$site_url modules_count=" . count($modules), 400 );
		}

		$data = [
			'site_url'   => $site_url,
			'overall'    => $overall,
			'modules'    => $modules,
			'generated'  => $generated,
		];

		try {
			$path = ( new \Codexpert\CX_Auditor\Report\PDF() )->generate( $data );
			error_log("PDF path: " . $path);
		} catch ( \Exception $e ) {
			wp_die( esc_html__( 'PDF generation failed: ', 'cx-auditor' ) . $e->getMessage(), 500 );
		}

		if ( ! $path || ! file_exists( $path ) ) {
			wp_die( esc_html__( 'Could not generate PDF.', 'cx-auditor' ) . " path=$path", 500 );
		}

		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="cx-audit-' . sanitize_file_name( $site_url ) . '.pdf"' );
		header( 'Content-Length: ' . filesize( $path ) );
		readfile( $path );
		exit;
	}
}