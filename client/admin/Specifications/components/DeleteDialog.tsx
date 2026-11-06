import { Button, Modal, Notice, Spinner } from '@wordpress/components';
import { __, sprintf, _n } from '@wordpress/i18n';
import { useSetUsage } from '../hooks/useSetUsage';
import { useProductValueUsage } from '../hooks/useProductValueUsage';
import type { SpecificationItem } from '../mapRecord';

interface Props {
	items: SpecificationItem[];
	isDeleting: boolean;
	onConfirm: () => void;
	onCancel: () => void;
}

const PURGE = __(
	'Deleting also removes this specification’s value from every product. This cannot be undone, and it is not optional: a deleted slug becomes free, and a specification created later with the same name would silently inherit those values.',
	'product-specifications'
);

const DeleteDialog = ( { items, isDeleting, onConfirm, onCancel }: Props ) => {
	const usage = useSetUsage( items );
	const productUsage = useProductValueUsage( items );
	const isImpactLoading = usage.isLoading || productUsage.isLoading;
	const hasImpactError = usage.hasError || productUsage.hasError;
	const isSingle = items.length === 1;
	const title = isSingle
		? sprintf(
				/* translators: %s: the specification being deleted. */
				__( 'Delete “%s”?', 'product-specifications' ),
				items[ 0 ]?.name ?? ''
		  )
		: sprintf(
				/* translators: %d: number of specifications being deleted. */
				__( 'Delete %d specifications?', 'product-specifications' ),
				items.length
		  );

	return (
		<Modal title={ title } onRequestClose={ onCancel } size="medium">
			{ isImpactLoading && (
				<p>
					<Spinner />{ ' ' }
					{ __(
						'Checking affected sets and products…',
						'product-specifications'
					) }
				</p>
			) }
			{ ! isImpactLoading && hasImpactError && (
				<Notice status="error" isDismissible={ false }>
					{ __(
						'The affected sets or product count could not be loaded. Deletion is unavailable until the impact can be checked.',
						'product-specifications'
					) }
				</Notice>
			) }
			{ ! isImpactLoading && ! hasImpactError && isSingle && (
				<SingleImpact
					item={ items[ 0 ] as SpecificationItem }
					sets={ usage.bySpecification[ items[ 0 ]?.id ?? 0 ] ?? [] }
					productCount={
						productUsage.bySpecification[ items[ 0 ]?.id ?? 0 ] ?? 0
					}
				/>
			) }
			{ ! isImpactLoading && ! hasImpactError && ! isSingle && (
				<CombinedImpact
					items={ items }
					usage={ usage }
					productUsage={ productUsage }
				/>
			) }
			<div className="ps-modal-actions">
				<Button variant="tertiary" onClick={ onCancel }>
					{ __( 'Cancel', 'product-specifications' ) }
				</Button>
				<Button
					variant="primary"
					isDestructive
					isBusy={ isDeleting }
					disabled={ isDeleting || isImpactLoading || hasImpactError }
					onClick={ onConfirm }
				>
					{ isSingle
						? __( 'Delete', 'product-specifications' )
						: sprintf(
								/* translators: %d: number of specifications being deleted. */
								__( 'Delete %d', 'product-specifications' ),
								items.length
						  ) }
				</Button>
			</div>
		</Modal>
	);
};

const SingleImpact = ( {
	item,
	sets,
	productCount,
}: {
	item: SpecificationItem;
	sets: string[];
	productCount: number;
} ) => {
	return (
		<>
			<p>
				<b>
					{ sets.length === 0
						? __(
								'No set uses this specification.',
								'product-specifications'
						  )
						: sprintf(
								/* translators: %d: number of sets that use the specification. */
								_n(
									'%d set uses this specification.',
									'%d sets use this specification.',
									sets.length,
									'product-specifications'
								),
								sets.length
						  ) }
				</b>
			</p>
			{ sets.length > 0 && (
				<ul className="ps-chips">
					{ sets.map( ( title ) => (
						<li
							key={ `${ item.id }-${ title }` }
							className="ps-chip"
						>
							{ title }
						</li>
					) ) }
				</ul>
			) }
			<p>
				<b>
					{ sprintf(
						/* translators: %d: number of products holding this value. */
						_n(
							'%d product has a stored value.',
							'%d products have a stored value.',
							productCount,
							'product-specifications'
						),
						productCount
					) }
				</b>
			</p>
			<Notice status="error" isDismissible={ false }>
				{ PURGE }
			</Notice>
		</>
	);
};

const CombinedImpact = ( {
	items,
	usage,
	productUsage,
}: {
	items: SpecificationItem[];
	usage: ReturnType< typeof useSetUsage >;
	productUsage: ReturnType< typeof useProductValueUsage >;
} ) => (
	<>
		<table className="ps-impact-table">
			<thead>
				<tr>
					<th>{ __( 'Specification', 'product-specifications' ) }</th>
					<th>{ __( 'Sets', 'product-specifications' ) }</th>
					<th>{ __( 'Products', 'product-specifications' ) }</th>
				</tr>
			</thead>
			<tbody>
				{ items.map( ( item ) => (
					<tr key={ item.id }>
						<td>{ item.name }</td>
						<td>
							{ usage.bySpecification[ item.id ]?.length ?? 0 }
						</td>
						<td>
							{ productUsage.bySpecification[ item.id ] ?? 0 }
						</td>
					</tr>
				) ) }
				<tr>
					<td>
						<b>{ __( 'Total', 'product-specifications' ) }</b>
					</td>
					<td>
						<b>
							{ sprintf(
								/* translators: %d: number of distinct sets across the selection. */
								_n(
									'%d set',
									'%d sets',
									usage.setCount,
									'product-specifications'
								),
								usage.setCount
							) }
						</b>
					</td>
					<td>
						<b>{ productUsage.total }</b>
					</td>
				</tr>
			</tbody>
		</table>
		<Notice status="error" isDismissible={ false }>
			{ __(
				'Deleting also removes these specifications’ values from every product. This cannot be undone, and it is not optional: a deleted slug becomes free, and a specification created later with the same name would silently inherit those values.',
				'product-specifications'
			) }
		</Notice>
	</>
);

export default DeleteDialog;
