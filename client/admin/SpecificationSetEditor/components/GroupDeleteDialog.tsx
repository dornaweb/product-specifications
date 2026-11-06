import { Button, Modal, SelectControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, sprintf, _n } from '@wordpress/i18n';
import type { StructureGroup } from '../state';

interface Props {
	group: StructureGroup;
	otherGroups: StructureGroup[];
	onConfirm: ( keepItemsIn: string | null ) => void;
	onCancel: () => void;
}

// Deleting a group asks what happens to what it holds. Nothing is discarded
// without an answer, and the answer says plainly that removing a reference is
// not deleting a specification.
const GroupDeleteDialog = ( {
	group,
	otherGroups,
	onConfirm,
	onCancel,
}: Props ) => {
	const [ keepItemsIn, setKeepItemsIn ] = useState(
		otherGroups[ 0 ]?.key ?? ''
	);
	const [ keeps, setKeeps ] = useState( otherGroups.length > 0 );
	const isEmpty = group.items.length === 0;

	return (
		<Modal
			title={ sprintf(
				/* translators: %s: the group being deleted. */
				__( 'Delete the group “%s”?', 'product-specifications' ),
				group.label
			) }
			onRequestClose={ onCancel }
			size="medium"
		>
			{ isEmpty ? (
				<p>
					{ __(
						'It is empty, so nothing goes with it.',
						'product-specifications'
					) }
				</p>
			) : (
				<>
					<p>
						{ sprintf(
							/* translators: %s: how many specifications the group holds. */
							__(
								'It holds %s. What should happen to them?',
								'product-specifications'
							),
							sprintf(
								/* translators: %d: number of specifications. */
								_n(
									'%d specification',
									'%d specifications',
									group.items.length,
									'product-specifications'
								),
								group.items.length
							)
						) }
					</p>
					{ otherGroups.length > 0 && (
						<div className="ps-choice">
							<input
								type="radio"
								id="ps-group-delete-move"
								name="ps-group-delete"
								checked={ keeps }
								onChange={ () => setKeeps( true ) }
							/>
							<span>
								<label htmlFor="ps-group-delete-move">
									<b>
										{ __(
											'Move them to another group',
											'product-specifications'
										) }
									</b>
								</label>
								<SelectControl
									__nextHasNoMarginBottom
									__next40pxDefaultSize
									label={ __(
										'Destination group',
										'product-specifications'
									) }
									hideLabelFromVision
									value={ keepItemsIn }
									options={ otherGroups.map( ( other ) => ( {
										value: other.key,
										label: other.label,
									} ) ) }
									onChange={ setKeepItemsIn }
								/>
							</span>
						</div>
					) }
					<div className="ps-choice">
						<input
							type="radio"
							id="ps-group-delete-remove"
							name="ps-group-delete"
							checked={ ! keeps }
							onChange={ () => setKeeps( false ) }
						/>
						<span>
							<label htmlFor="ps-group-delete-remove">
								<b>
									{ __(
										'Remove them from this set',
										'product-specifications'
									) }
								</b>
							</label>
							<span className="ps-choice__help">
								{ __(
									'The specifications themselves are not deleted, and no product loses a value.',
									'product-specifications'
								) }
							</span>
						</span>
					</div>
				</>
			) }
			<div className="ps-modal-actions">
				<Button variant="tertiary" onClick={ onCancel }>
					{ __( 'Cancel', 'product-specifications' ) }
				</Button>
				<Button
					variant="primary"
					isDestructive
					onClick={ () =>
						onConfirm( keeps && ! isEmpty ? keepItemsIn : null )
					}
				>
					{ __( 'Delete group', 'product-specifications' ) }
				</Button>
			</div>
		</Modal>
	);
};

export default GroupDeleteDialog;
