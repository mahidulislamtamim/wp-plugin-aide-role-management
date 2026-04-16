<?php
/**
 * Rules list table for the admin UI.
 *
 * @package aiderolemanagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Display auto-assign rules using WP_List_Table.
 */
final class Aide_Role_Rules_Table extends WP_List_Table {
	/**
	 * Raw rules array from the option.
	 *
	 * @var array<string, array>
	 */
	private $rules = [];

	/**
	 * @param array<string, array> $rules Rules from option.
	 */
	public function __construct( $rules ) {
		parent::__construct(
			[
				'singular' => 'aide_role_rule',
				'plural'   => 'aide_role_rules',
				'ajax'     => false,
			]
		);

		$this->rules = is_array( $rules ) ? $rules : [];
	}

	/**
	 * @return array<string, string>
	 */
	public function get_columns() {
		return [
			'trigger'   => __( 'Trigger Role', 'aiderolemanagement' ),
			'assigned'  => __( 'Assigned Role', 'aiderolemanagement' ),
			'createdAt' => __( 'Date Added', 'aiderolemanagement' ),
			'actions'   => __( 'Actions', 'aiderolemanagement' ),
		];
	}

	/**
	 * @return array<string, array{0:string,1:bool}>
	 */
	protected function get_sortable_columns() {
		return [
			'trigger'   => [ 'trigger', false ],
			'assigned'  => [ 'assigned', false ],
			'createdAt' => [ 'createdAt', true ],
		];
	}

	/**
	 * @param array $item Item.
	 * @param string $column_name Column name.
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		if ( 'actions' === $column_name ) {
			$rule_id = isset( $item['id'] ) ? (string) $item['id'] : '';
			if ( '' === $rule_id ) {
				return '';
			}

			$delete_url = wp_nonce_url(
				add_query_arg(
					[
						'page'    => 'aide-role',
						'action'  => 'delete_rule',
						'rule_id' => $rule_id,
					],
					admin_url( 'admin.php' )
				),
				'aide_role_delete_rule_' . $rule_id,
				'aide_role_management_nonce'
			);

			return sprintf(
				'<a class="button button-secondary" href="%1$s" onclick="return confirm(\'%2$s\');">%3$s</a>',
				esc_url( $delete_url ),
				esc_js( __( 'Are you sure you want to delete this rule?', 'aiderolemanagement' ) ),
				esc_html__( 'Delete', 'aiderolemanagement' )
			);
		}

		if ( 'createdAt' === $column_name ) {
			$value = isset( $item['createdAt'] ) ? (string) $item['createdAt'] : '';
			if ( '' === $value ) {
				return '';
			}

			$ts = strtotime( $value );
			if ( ! $ts ) {
				return esc_html( $value );
			}

			return esc_html( date_i18n( get_option( 'date_format' ), $ts ) );
		}

		return isset( $item[ $column_name ] ) ? esc_html( (string) $item[ $column_name ] ) : '';
	}

	/**
	 * Render only the bottom table navigation.
	 *
	 * WordPress by default renders `.tablenav.top` and `.tablenav.bottom`. The
	 * user requested no top nav for this table.
	 *
	 * @param string $which 'top' or 'bottom'.
	 * @return void
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			return;
		}

		parent::display_tablenav( $which );
	}

	/**
	 * @return void
	 */
	public function prepare_items() {
		$items = [];

		foreach ( $this->rules as $rule_id => $data ) {
			if ( ! is_array( $data ) ) {
				continue;
			}

			$trigger  = isset( $data['trigger'] ) ? sanitize_key( (string) $data['trigger'] ) : '';
			$assigned = isset( $data['assigned'] ) ? sanitize_key( (string) $data['assigned'] ) : '';
			$date     = isset( $data['createdAt'] ) ? (string) $data['createdAt'] : (string) ( $data['date'] ?? '' ); // Back-compat.

			$items[] = [
				'id'        => (string) $rule_id,
				'trigger'   => $trigger,
				'assigned'  => $assigned,
				'createdAt' => $date,
			];
		}

		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( (string) wp_unslash( $_REQUEST['s'] ) ) : '';
		if ( '' !== $search ) {
			$items = array_values(
				array_filter(
					$items,
					static function ( $item ) use ( $search ) {
						$haystack = strtolower( (string) ( $item['trigger'] . ' ' . $item['assigned'] ) );
						return false !== strpos( $haystack, strtolower( $search ) );
					}
				)
			);
		}

		$orderby = isset( $_REQUEST['orderby'] ) ? sanitize_key( (string) wp_unslash( $_REQUEST['orderby'] ) ) : 'createdAt';
		$order   = isset( $_REQUEST['order'] ) ? sanitize_key( (string) wp_unslash( $_REQUEST['order'] ) ) : 'desc';

		usort(
			$items,
			static function ( $a, $b ) use ( $orderby, $order ) {
				$va = $a[ $orderby ] ?? '';
				$vb = $b[ $orderby ] ?? '';

				if ( 'createdAt' === $orderby ) {
					$va = strtotime( (string) $va ) ?: 0;
					$vb = strtotime( (string) $vb ) ?: 0;
				}

				if ( $va === $vb ) {
					return 0;
				}

				$cmp = ( $va < $vb ) ? -1 : 1;
				return ( 'desc' === strtolower( (string) $order ) ) ? -$cmp : $cmp;
			}
		);

		$per_page     = 20;
		$current_page = max( 1, (int) $this->get_pagenum() );
		$total_items  = count( $items );

		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => $per_page,
			]
		);

		$this->items = array_slice( $items, ( $current_page - 1 ) * $per_page, $per_page );
		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];
	}
}

