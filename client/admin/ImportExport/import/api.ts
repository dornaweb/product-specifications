import { __ } from '@wordpress/i18n';
import type { ImportPayload, ImportReport } from './types';

export class ImportRequestError extends Error {
	constructor(
		message: string,
		readonly details: string[] = []
	) {
		super( message );
	}
}

interface WordPressErrorResponse {
	message?: string;
	data?: { errors?: string[] };
}

async function request< Payload = ImportPayload >(
	method: 'GET' | 'POST' | 'DELETE',
	action = '',
	body?: FormData
): Promise< Payload > {
	const endpoint = window.productSpecificationsAdmin?.importEndpoint;

	if ( ! endpoint ) {
		throw new ImportRequestError(
			__(
				'The import endpoint is not available.',
				'product-specifications'
			)
		);
	}

	const response = await window.fetch( endpoint + action, {
		method,
		body,
		credentials: 'same-origin',
		headers: {
			'X-WP-Nonce': window.productSpecificationsAdmin?.restNonce ?? '',
		},
	} );
	const payload = ( await response.json() ) as
		| Payload
		| WordPressErrorResponse;

	if ( ! response.ok ) {
		const failure = payload as WordPressErrorResponse;

		throw new ImportRequestError(
			failure.message ??
				__( 'The import request failed.', 'product-specifications' ),
			failure.data?.errors ?? []
		);
	}

	return payload as Payload;
}

export const fetchImport = () => request( 'GET' );

export const uploadPackage = ( file: File ) => {
	const body = new window.FormData();
	body.append( 'file', file );

	return request( 'POST', '', body );
};

export const startImport = () => request( 'POST', '/start' );

export const discardImport = () => request( 'DELETE' );

export const fetchReport = () => request< ImportReport >( 'GET', '/report' );
