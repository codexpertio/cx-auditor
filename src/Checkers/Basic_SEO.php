<?php
namespace Codexpert\CX_Auditor\Checkers;

class Basic_SEO extends Abstract_Checker {

	public function slug(): string {
		return 'basic_seo';
	}

	public function label(): string {
		return __( 'Basic SEO', 'cx-auditor' );
	}

	protected function do_checks( string $site_url ): array {
		$checks  = [];
		$url     = rtrim( $site_url, '/' );

		$res = $this->http_get( $url );
		if ( ! $res ) {
			return $checks;
		}

		$body = $res['body'];

		if ( preg_match( '/<title[^>]*>([^<]+)<\/title>/i', $body, $matches ) ) {
			$checks[] = $this->check( 'title_tag', __( 'Title Tag', 'cx-auditor' ), 'pass', trim( $matches[1] ), null );
		} else {
			$checks[] = $this->check( 'title_tag', __( 'Title Tag', 'cx-auditor' ), 'fail', 'missing', 'present' );
			$this->recommend( __( 'Add a title tag to the homepage.', 'cx-auditor' ) );
		}

		$has_description = preg_match( '/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']/i', $body, $matches )
			|| preg_match( '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+name=["\']description["\']/i', $body, $matches );

		if ( $has_description && ! empty( $matches[1] ) ) {
			$checks[] = $this->check( 'meta_description', __( 'Meta Description', 'cx-auditor' ), 'pass', substr( $matches[1], 0, 60 ) . '...', null );
		} else {
			$checks[] = $this->check( 'meta_description', __( 'Meta Description', 'cx-auditor' ), 'warning', 'missing', 'present' );
			$this->recommend( __( 'Add a meta description to the homepage.', 'cx-auditor' ) );
		}

		$res = $this->http_get( $url . '/robots.txt' );
		$robots = $res && 200 === $res['code'];
		$checks[] = $this->check(
			'robots_txt',
			__( 'robots.txt', 'cx-auditor' ),
			$robots ? 'pass' : 'warning',
			$robots ? 'present' : 'missing',
			'present'
		);
		if ( ! $robots ) {
			$this->recommend( __( 'Create a robots.txt file.', 'cx-auditor' ) );
		}

		$sitemap_urls = [
			'/sitemap.xml',
			'/sitemap_index.xml',
			'/wp-sitemap.xml',
		];
		$sitemap_found = false;
		foreach ( $sitemap_urls as $sitemap ) {
			$res = $this->http_get( $url . $sitemap );
			if ( $res && 200 === $res['code'] ) {
				$sitemap_found = true;
				break;
			}
		}
		$checks[] = $this->check(
			'sitemap',
			__( 'XML Sitemap', 'cx-auditor' ),
			$sitemap_found ? 'pass' : 'warning',
			$sitemap_found ? 'present' : 'missing',
			'present'
		);
		if ( ! $sitemap_found ) {
			$this->recommend( __( 'Add an XML sitemap for search engines.', 'cx-auditor' ) );
		}

		$blog_public = true;
		if ( preg_match( '/<meta[^>]+name=["\']robots["\'][^>]+content=["\']noindex["\']/i', $body )
			|| preg_match( '/<meta[^>]+content=["\']noindex["\'][^>]+name=["\']robots["\']/i', $body ) ) {
			$blog_public = false;
		}
		$checks[] = $this->check(
			'search_visibility',
			__( 'Search Visibility', 'cx-auditor' ),
			$blog_public ? 'pass' : 'warning',
			$blog_public ? 'public' : 'noindex',
			'public'
		);

		return $checks;
	}
}