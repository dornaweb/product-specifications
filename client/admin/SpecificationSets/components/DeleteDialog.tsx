import { Button, Modal } from '@wordpress/components';
import { __, sprintf, _n } from '@wordpress/i18n';
import type { SpecificationSetItem } from '../fields';

interface Props {
	items: SpecificationSetItem[];
	isDeleting: boolean;
	onConfirm: () => void;
	onCancel: () => void;
}

// Values are keyed by specification slug, not by set, so deleting a set leaves
// every product's values where they are. A merchant assumes the opposite, so the
// dialog says it.
const DeleteDialog = ( { items, isDeleting, onConfirm, onCancel }: Props ) => {
	const products = items.reduce(
		( total, item ) => total + item.products,
		0
	);
	const title =
		items.length === 1
			? sprintf(
					/* translators: %s: the set being deleted. */
					__( 'Delete “%s”?', 'product-specifications' ),
					items[ 0 ]?.title ?? ''
			  )
			: sprintf(
					/* translators: %d: number of sets being deleted. */
					__( 'Delete %d sets?', 'product-specifications' ),
					items.length
			  );

	return (
		<Modal title={ title } onRequestClose={ onCancel } size="medium">
			{ products > 0 ? (
				<p>
					{ sprintf(
						/* translators: %s: how many products the selection is assigned to. */
						__(
							'%s assigned to it, and will show no specifications table until another set is assigned.',
							'product-specifications'
						),
						sprintf(
							/* translators: %d: number of products. */
							_n(
								'%d product is',
								'%d products are',
								products,
								'product-specifications'
							),
							products
						)
					) }
				</p>
			) : (
				<p>
					{ __(
						'No product is assigned to it.',
						'product-specifications'
					) }
				</p>
			) }
			<p>
				{ __(
					'The values stored on those products are kept. They belong to the specifications themselves, not to this set, so assigning another set shows them again.',
					'product-specifications'
				) }
			</p>
			{ items.length > 1 && (
				<ul className="ps-chips">
					{ items.map( ( item ) => (
						<li key={ item.id } className="ps-chip">
							{ item.title }
						</li>
					) ) }
				</ul>
			) }
			<div className="ps-modal-actions">
				<Button variant="tertiary" onClick={ onCancel }>
					{ __( 'Cancel', 'product-specifications' ) }
				</Button>
				<Button
					variant="primary"
					isDestructive
					isBusy={ isDeleting }
					disabled={ isDeleting }
					onClick={ onConfirm }
				>
					{ __( 'Delete', 'product-specifications' ) }
				</Button>
			</div>
		</Modal>
	);
};

export default DeleteDialog;
