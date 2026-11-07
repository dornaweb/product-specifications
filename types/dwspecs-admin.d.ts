declare global {
	interface Window {
		dwspecsAdmin?: {
			capability: string;
			settingsCapability: string;
			settingsEndpoint: string;
			restNonce: string;
			migrationRequired: boolean;
			migrationEndpoint: string;
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
