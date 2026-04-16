<?php
namespace Codexpert\CX_Auditor\Admin;

defined( 'ABSPATH' ) || exit;

class Assets {

	public function register(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	public function enqueue( string $hook ): void {
		if ( ! str_contains( (string) $hook, 'cx-auditor' ) ) {
			return;
		}

		wp_enqueue_style(
			'cx-auditor-admin',
			CX_AUDITOR_URL . 'assets/css/admin.css',
			[],
			CX_AUDITOR_VERSION
		);

		wp_enqueue_script(
			'cx-auditor-admin',
			CX_AUDITOR_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			CX_AUDITOR_VERSION,
			true
		);

		wp_localize_script(
			'cx-auditor-admin',
			'CXAuditor',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'cx_auditor_audit' ),
				'i18n'    => [
					'enterUrl'   => __( 'Please enter a valid site URL.', 'cx-auditor' ),
					'scanning'  => __( 'Scanning site…', 'cx-auditor' ),
					'error'     => __( 'Could not reach the site. It may be down or blocking requests.', 'cx-auditor' ),
					'noWordPress' => __( 'This does not appear to be a WordPress site.', 'cx-auditor' ),
				],
			]
		);
	}
}