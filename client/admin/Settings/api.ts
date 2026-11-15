import type {
	SettingsRequestFailure,
	SettingsResponse,
	SettingValue,
} from './types';

interface WordPressErrorResponse {
	message?: string;
	data?: {
		fields?: Record< string, string >;
	};
}

export class SettingsApiError extends Error {
	readonly fields: Record< string, string >;

	constructor( failure: SettingsRequestFailure ) {
		super( failure.message );
		this.fields = failure.fields;
	}
}

async function request(
	method: 'GET' | 'POST',
	settings?: Record< string, SettingValue >
): Promise< SettingsResponse > {
	const endpoint = window.productSpecificationsAdmin?.settingsEndpoint;
	if ( ! endpoint ) {
		throw new SettingsApiError( {
			message: 'The settings endpoint is not available.',
			fields: {},
		} );
	}

	const response = await window.fetch( endpoint, {
		method,
		credentials: 'same-origin',
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': window.productSpecificationsAdmin?.restNonce ?? '',
		},
		body: settings ? JSON.stringify( { settings } ) : undefined,
	} );
	const payload = ( await response.json() ) as
		| SettingsResponse
		| WordPressErrorResponse;

	if ( ! response.ok ) {
		const failure = payload as WordPressErrorResponse;
		throw new SettingsApiError( {
			message: failure.message ?? 'The settings request failed.',
			fields: failure.data?.fields ?? {},
		} );
	}

	return payload as SettingsResponse;
}

export function fetchSettings(): Promise< SettingsResponse > {
	return request( 'GET' );
}

export function saveSettings(
	settings: Record< string, SettingValue >
): Promise< SettingsResponse > {
	return request( 'POST', settings );
}
