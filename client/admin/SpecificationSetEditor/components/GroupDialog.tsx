import { Button, Modal, TextControl } from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { displayShortcut, isKeyboardEvent } from '@wordpress/keycodes';
import { Shortcut } from '../../shared/ui';

interface Props {
	label: string;
	isNew: boolean;
	onConfirm: ( label: string ) => void;
	onCancel: () => void;
}

// Naming a group and renaming one are the same act, so they are the same
// dialog: a drop opens it pre-filled, Add group opens it with a default, and
// Edit opens it with the current name.
const GroupDialog = ( { label, isNew, onConfirm, onCancel }: Props ) => {
	const [ value, setValue ] = useState( label );
	const fieldRef = useRef< HTMLDivElement | null >( null );

	// The dialog exists to be typed in, so it opens with the field ready.
	useEffect( () => {
		fieldRef.current?.querySelector( 'input' )?.select();
	}, [] );

	const confirm = () => {
		if ( value.trim() ) {
			onConfirm( value.trim() );
		}
	};

	return (
		<Modal
			title={
				isNew
					? __( 'Name this group', 'product-specifications' )
					: __( 'Edit group', 'product-specifications' )
			}
			onRequestClose={ onCancel }
			size="small"
		>
			<div
				onKeyDown={ ( event ) => {
					if ( isKeyboardEvent.primary( event, 'Enter' ) ) {
						event.preventDefault();
						confirm();
					}
				} }
				role="presentation"
			>
				<div ref={ fieldRef }>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Group label', 'product-specifications' ) }
						help={ __(
							'Shown as the heading above these specifications on the storefront, if your layout uses group headings.',
							'product-specifications'
						) }
						value={ value }
						onChange={ setValue }
					/>
				</div>
				<div className="ps-modal-actions">
					<Button variant="tertiary" onClick={ onCancel }>
						{ __( 'Cancel', 'product-specifications' ) }
						<Shortcut keys="Esc" />
					</Button>
					<Button
						variant="primary"
						disabled={ ! value.trim() }
						onClick={ confirm }
					>
						{ isNew
							? __( 'Create group', 'product-specifications' )
							: __( 'Save', 'product-specifications' ) }
						<Shortcut keys={ displayShortcut.primary( '↵' ) } />
					</Button>
				</div>
			</div>
		</Modal>
	);
};

export default GroupDialog;
