declare global {
	interface PickedAttachment {
		id: number;
		title: string;
		url: string;
	}

	interface MediaFrame {
		on( event: 'select', callback: () => void ): void;
		open(): void;
		state(): {
			get( key: 'selection' ): { toJSON(): PickedAttachment[] };
		};
	}

	interface Window {
		wp?: {
			media?: ( options: {
				title: string;
				button: { text: string };
				multiple: boolean;
			} ) => MediaFrame;
		};
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
