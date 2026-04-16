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
}