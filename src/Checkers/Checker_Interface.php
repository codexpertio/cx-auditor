<?php
namespace Codexpert\CX_Auditor\Checkers;

defined( 'ABSPATH' ) || exit;

interface Checker_Interface {

	public function slug(): string;

	public function label(): string;

	public function run( string $site_url ): array;
}