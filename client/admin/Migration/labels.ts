import { __, _n, sprintf } from '@wordpress/i18n';

export function stepTitle( step: string ): string {
	switch ( step ) {
		case 'specifications':
			return __( 'Converting specifications', 'product-specifications' );
		case 'sets':
			return __(
				'Converting tables into specification sets',
				'product-specifications'
			);
		case 'values':
			return __( 'Migrating product values', 'product-specifications' );
		default:
			return step;
	}
}

export function scopeLabel( step: string, count: number ): string {
	switch ( step ) {
		case 'specifications':
			return _n(
				'specification',
				'specifications',
				count,
				'product-specifications'
			);
		case 'sets':
			return _n(
				'specification table',
				'specification tables',
				count,
				'product-specifications'
			);
		case 'values':
			return _n(
				'product with values',
				'products with values',
				count,
				'product-specifications'
			);
		default:
			return step;
	}
}

export function itemLabel( step: string, item: number ): string {
	switch ( step ) {
		case 'specifications':
			return sprintf(
				/* translators: %d: a specification's id. */
				__( 'Specification #%d', 'product-specifications' ),
				item
			);
		case 'sets':
			/* translators: %d: a specification set's id. */
			return sprintf( __( 'Set #%d', 'product-specifications' ), item );
		default:
			return sprintf(
				/* translators: %d: a product's id. */
				__( 'Product #%d', 'product-specifications' ),
				item
			);
	}
}
