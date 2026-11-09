declare global {
	interface Window {
		productSpecificationsAdmin?: {
			pages: {
				path: string;
				title: string;
				capability: string;
			}[];
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
		productSpecificationsProductData?: {
			choices: {
				source: string;
				name: string;
				label: string;
				group: string;
			}[];
		};
	}
}

export {};
