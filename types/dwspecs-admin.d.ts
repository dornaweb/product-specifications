declare global {
	interface Window {
		dwspecsAdmin?: {
			capability: string;
			settingsCapability: string;
			settingsEndpoint: string;
			restNonce: string;
		};
	}
}

export {};
