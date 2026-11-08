import { __ } from '@wordpress/i18n';
import type { OutcomeStatus } from './types';

export const sectionTitle = ( key: string ) =>
	( {
		specifications: __( 'Specifications', 'product-specifications' ),
		sets: __( 'Specification sets', 'product-specifications' ),
		values: __( 'Product values', 'product-specifications' ),
	} )[ key ] ?? key;

export const OUTCOME_ORDER: OutcomeStatus[] = [
	'create',
	'update',
	'unchanged',
	'conflict',
	'skip',
	'failed',
];

export const outcomeTitle = ( status: OutcomeStatus ) =>
	( {
		create: __( 'New', 'product-specifications' ),
		update: __( 'Updated', 'product-specifications' ),
		unchanged: __( 'Unchanged', 'product-specifications' ),
		conflict: __( 'Type conflict', 'product-specifications' ),
		skip: __( 'Skipped', 'product-specifications' ),
		failed: __( 'Failed', 'product-specifications' ),
	} )[ status ];
