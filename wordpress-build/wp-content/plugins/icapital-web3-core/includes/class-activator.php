<?php
/**
 * Plugin activation — creates custom database tables.
 * Uses dbDelta() to safely create or update tables.
 * @package icapital-web3-core
 */
defined( 'ABSPATH' ) || exit;

class ICapital_Activator {

	public static function activate() : void {
		self::create_tables();
		flush_rewrite_rules();
	}

	public static function deactivate() : void {
		flush_rewrite_rules();
	}

	private static function create_tables() : void {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// ── Virtual Balances ─────────────────────────────────
		dbDelta( "CREATE TABLE {$wpdb->prefix}icapital_virtual_balances (
			id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id      BIGINT UNSIGNED NOT NULL,
			asset_symbol VARCHAR(20)     NOT NULL,
			amount       DECIMAL(20,8)   NOT NULL DEFAULT 0,
			updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY   user_asset (user_id, asset_symbol),
			KEY          user_id (user_id)
		) $charset;" );

		// ── Transactions ──────────────────────────────────────
		dbDelta( "CREATE TABLE {$wpdb->prefix}icapital_transactions (
			id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id      BIGINT UNSIGNED NOT NULL,
			type         VARCHAR(30)     NOT NULL,
			asset_symbol VARCHAR(20)     NOT NULL,
			amount       DECIMAL(20,8)   NOT NULL,
			status       VARCHAR(20)     NOT NULL DEFAULT 'COMPLETED',
			tx_hash      VARCHAR(255)    NULL,
			created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY          user_id (user_id)
		) $charset;" );

		// ── Wallet Assets Master List ─────────────────────────
		dbDelta( "CREATE TABLE {$wpdb->prefix}icapital_wallet_assets (
			id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			symbol       VARCHAR(20)     NOT NULL,
			name         VARCHAR(100)    NOT NULL,
			network      VARCHAR(50)     NOT NULL DEFAULT 'Ethereum',
			icon_color   VARCHAR(20)     NOT NULL DEFAULT '#627EEA',
			icon_url     VARCHAR(255)    NULL,
			price        DECIMAL(20,8)   NOT NULL DEFAULT 0,
			price_change DECIMAL(10,4)   NOT NULL DEFAULT 0,
			updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY   symbol (symbol)
		) $charset;" );

		// ── LLC Registrations (mirrors CPT meta) ──────────────
		dbDelta( "CREATE TABLE {$wpdb->prefix}icapital_llc_registrations (
			id                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id              BIGINT UNSIGNED NOT NULL,
			post_id              BIGINT UNSIGNED NULL,
			company_name         VARCHAR(255)    NOT NULL,
			designator           VARCHAR(20)     NOT NULL DEFAULT 'LLC',
			state                VARCHAR(5)      NOT NULL DEFAULT 'WY',
			industry             VARCHAR(100)    NULL,
			registered_agent     TINYINT(1)      NOT NULL DEFAULT 1,
			mail_forwarding      TINYINT(1)      NOT NULL DEFAULT 0,
			ein_application      TINYINT(1)      NOT NULL DEFAULT 0,
			crypto_protection    TINYINT(1)      NOT NULL DEFAULT 0,
			wallet_shipping_addr TEXT            NULL,
			roi_tracking_status  VARCHAR(30)     NOT NULL DEFAULT 'PENDING_ACTIVATION',
			legal_docs_sent      TINYINT(1)      NOT NULL DEFAULT 0,
			status               VARCHAR(30)     NOT NULL DEFAULT 'PENDING_PAYMENT',
			state_fee            DECIMAL(10,2)   NOT NULL DEFAULT 120.00,
			created_at           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY          (id),
			KEY                  user_id (user_id)
		) $charset;" );

		// ── Seed default wallet assets ────────────────────────
		self::seed_wallet_assets();
	}

	private static function seed_wallet_assets() : void {
		global $wpdb;
		$table  = $wpdb->prefix . 'icapital_wallet_assets';
		$exists = $wpdb->get_var( "SELECT COUNT(*) FROM $table" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $exists > 0 ) {
			return; // Already seeded
		}

		$assets = [
			[ 'symbol' => 'ETH',  'name' => 'Ethereum',  'network' => 'Ethereum',  'icon_color' => '#627EEA', 'price' => 3200.00, 'price_change' =>  2.5 ],
			[ 'symbol' => 'BTC',  'name' => 'Bitcoin',   'network' => 'Bitcoin',   'icon_color' => '#F7931A', 'price' => 65000.00,'price_change' =>  1.2 ],
			[ 'symbol' => 'USDT', 'name' => 'Tether',    'network' => 'Ethereum',  'icon_color' => '#26A17B', 'price' => 1.00,    'price_change' =>  0.0 ],
			[ 'symbol' => 'BNB',  'name' => 'BNB',       'network' => 'BSC',       'icon_color' => '#F3BA2F', 'price' => 580.00,  'price_change' =>  3.1 ],
			[ 'symbol' => 'SOL',  'name' => 'Solana',    'network' => 'Solana',    'icon_color' => '#9945FF', 'price' => 145.00,  'price_change' => -0.8 ],
			[ 'symbol' => 'MATIC','name' => 'Polygon',   'network' => 'Polygon',   'icon_color' => '#8247E5', 'price' => 0.88,    'price_change' =>  4.2 ],
		];

		foreach ( $assets as $asset ) {
			$wpdb->insert( $table, $asset ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}
	}
}
