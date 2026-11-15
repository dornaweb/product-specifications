import { __ } from '@wordpress/i18n';

export function canOpenMediaLibrary(): boolean {
	return typeof window.wp?.media === 'function';
}

export function openMediaLibrary(
	onPick: ( attachments: PickedAttachment[] ) => void
): void {
	const media = window.wp?.media;

	if ( ! media ) {
		return;
	}

	const frame = media( {
		title: __( 'Add documents', 'product-specifications' ),
		button: { text: __( 'Add', 'product-specifications' ) },
		multiple: true,
	} );

	frame.on( 'select', () =>
		onPick( frame.state().get( 'selection' ).toJSON() )
	);
	frame.open();
}
