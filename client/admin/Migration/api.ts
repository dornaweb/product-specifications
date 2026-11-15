import { __ } from '@wordpress/i18n';
import type { MigrationStatus } from './types';

interface WordPressErrorResponse {
	message?: string;
}

async function request(
	method: 'GET' | 'POST',
	action = ''
): Promise< MigrationStatus > {
	const endpoint = window.productSpecificationsAdmin?.migrationEndpoint;

	if ( ! endpoint ) {
		throw new Error(
			__(
				'The migration endpoint is not available.',
				'product-specifications'
			)
		);
	}

	const response = await window.fetch( endpoint + action, {
		method,
		credentials: 'same-origin',
		headers: {
			'X-WP-Nonce': window.productSpecificationsAdmin?.restNonce ?? '',
		},
	} );
	const payload = ( await response.json() ) as
		| MigrationStatus
		| WordPressErrorResponse;

	if ( ! response.ok ) {
		throw new Error(
			( payload as WordPressErrorResponse ).message ??
				__( 'The migration request failed.', 'product-specifications' )
		);
	}

	return payload as MigrationStatus;
}

export const fetchMigration = () => request( 'GET' );

export const startMigration = () => request( 'POST', '/start' );

export const resumeMigration = () => request( 'POST', '/resume' );
