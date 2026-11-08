declare global {
	interface Window {
		productSpecificationsAdmin?: {
			capability: string;
			settingsCapability: string;
			settingsEndpoint: string;
			restNonce: string;
			migrationRequired: boolean;
			migrationEndpoint: string;
			exportUrl: string;
			importEndpoint: string;
			migrationNotice: {
				status: 'warning' | 'info' | 'error';
				message: string;
				action: string;
				url: string;
			} | null;
		};
	}
}

export {};
