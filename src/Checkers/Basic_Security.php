<?php
namespace Codexpert\CX_Auditor\Checkers;

class Basic_Security extends Abstract_Checker {

	public function slug(): string {
		return 'basic_security';
	}

	public function label(): string {
		return __( 'Basic Security', 'cx-auditor' );
	}

	protected function do_checks( string $site_url ): array {
		$checks  = [];
		$url     = rtrim( $site_url, '/' );

		$res = $this->http_get( $url . '/xmlrpc.php' );
		$xmlrpc = $res && 200 === $res['code'];
		$checks[] = $this->check(
			'xmlrpc_enabled',
			__( 'XML-RPC Enabled', 'cx-auditor' ),
			$xmlrpc ? 'warning' : 'pass',
			$xmlrpc ? 'yes' : 'no',
			'no'
		);
		if ( $xmlrpc ) {
			$this->recommend( __( 'XML-RPC is enabled. Consider disabling it if not needed.', 'cx-auditor' ) );
		}

		$res = $this->http_get( $url . '/wp-login.php' );
		$login = $res && 200 === $res['code'];
		if ( $login ) {
			$checks[] = $this->check( 'default_login', __( 'Default Login URL', 'cx-auditor' ), 'pass', 'detected', 'custom' );
		}

		$res = $this->http_get( $url );
		if ( $res ) {
			$headers = $res['headers']->getAll();
			$security_headers = [
				'x-frame-options' => 'X-Frame-Options',
				'x-content-type-options' => 'X-Content-Type-Options',
				'strict-transport-security' => 'Strict-Transport-Security',
			];
			$missing = [];
			foreach ( $security_headers as $key => $label ) {
				if ( empty( $headers[ $key ] ) ) {
					$missing[] = $label;
				}
			}
			if ( ! empty( $missing ) ) {
				$checks[] = $this->check(
					'security_headers',
					__( 'Security Headers', 'cx-auditor' ),
					'warning',
					'部分缺失',
					'完整',
					[ 'missing' => $missing ]
				);
				$this->recommend( __( 'Add security headers (X-Frame-Options, X-Content-Type-Options, HSTS).', 'cx-auditor' ) );
			} else {
				$checks[] = $this->check( 'security_headers', __( 'Security Headers', 'cx-auditor' ), 'pass', 'all present', 'all present' );
			}
		}

		return $checks;
	}
}