<?php
namespace Codexpert\CX_Auditor\Checkers;

class WordPress_Detection extends Abstract_Checker {

	public function slug(): string {
		return 'wordpress_detection';
	}

	public function label(): string {
		return __( 'WordPress Detection', 'cx-auditor' );
	}

	protected function do_checks( string $site_url ): array {
		$checks = [];
		$url    = rtrim( $site_url, '/' );

		$res = $this->http_get( $url );
		if ( ! $res || 200 !== $res['code'] ) {
			$checks[] = $this->check( 'site_reachable', __( 'Site Reachable', 'cx-auditor' ), 'fail', 'no', 'yes' );
			$this->recommend( __( 'The site is not reachable. It may be down or blocking external requests.', 'cx-auditor' ) );
			return $checks;
		}

		$checks[] = $this->check( 'site_reachable', __( 'Site Reachable', 'cx-auditor' ), 'pass', 'yes', 'yes' );

		$body   = $res['body'];
		$headers = $res['headers']->getAll();

		$wp_indicators = 0;

		if ( preg_match( '/<meta name="generator" content="WordPress[^"]*"/i', $body ) ) {
			$wp_indicators++;
		}
		if ( preg_match( '/wp-content/i', $body ) ) {
			$wp_indicators++;
		}
		if ( preg_match( '/wp-includes/i', $body ) ) {
			$wp_indicators++;
		}
		if ( isset( $headers['x-powered-by'] ) && preg_match( '/WordPress/i', $headers['x-powered-by'] ) ) {
			$wp_indicators++;
		}

		if ( $wp_indicators > 0 ) {
			$checks[] = $this->check( 'is_wordpress', __( 'Is WordPress', 'cx-auditor' ), 'pass', 'yes', 'yes' );
		} else {
			$checks[] = $this->check( 'is_wordpress', __( 'Is WordPress', 'cx-auditor' ), 'fail', 'no', 'yes' );
			$this->recommend( __( 'This does not appear to be a WordPress site.', 'cx-auditor' ) );
		}

		if ( preg_match( '/<meta name="generator" content="WordPress ([\d.]+)"/i', $body, $matches ) ) {
			$checks[] = $this->check( 'wp_version', __( 'WordPress Version', 'cx-auditor' ), 'pass', $matches[1], null );
		}

		return $checks;
	}
}