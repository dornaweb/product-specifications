import { Button, Modal, Notice, Spinner } from '@wordpress/components';
import { __, sprintf, _n } from '@wordpress/i18n';
import { useSetUsage } from '../hooks/useSetUsage';
import type { SpecificationItem } from '../mapRecord';

interface Props {
	items: SpecificationItem[];
	isDeleting: boolean;
	onConfirm: () => void;
	onCancel: () => void;
}

const VALUES_KEPT = __(
	'Products that use this specification keep its value in their data. It is no longer shown anywhere, and it is removed when each product is next saved. Deleting cannot be undone.',
	'product-specifications'
);

const VALUES_KEPT_PLURAL = __(
	'Products that use these specifications keep their values in their data. They are no longer shown anywhere, and they are removed when each product is next saved. Deleting cannot be undone.',
	'product-specifications'
);

const DeleteDialog = ( { items, isDeleting, onConfirm, onCancel }: Props ) => {
	const usage = useSetUsage( items );
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
			{ usage.isLoading && (
				<p>
					<Spinner />{ ' ' }
					{ __(
						'Checking affected sets…',
						'product-specifications'
					) }
				</p>
			) }
			{ ! usage.isLoading && usage.hasError && (
				<Notice status="error" isDismissible={ false }>
					{ __(
						'The affected sets could not be loaded. Deletion is unavailable until the impact can be checked.',
						'product-specifications'
					) }
				</Notice>
			) }
			{ ! usage.isLoading && ! usage.hasError && isSingle && (
				<SingleImpact
					item={ items[ 0 ] as SpecificationItem }
					sets={ usage.bySpecification[ items[ 0 ]?.id ?? 0 ] ?? [] }
				/>
			) }
			{ ! usage.isLoading && ! usage.hasError && ! isSingle && (
				<CombinedImpact items={ items } usage={ usage } />
			) }
			<div className="ps-modal-actions">
				<Button variant="tertiary" onClick={ onCancel }>
					{ __( 'Cancel', 'product-specifications' ) }
				</Button>
				<Button
					variant="primary"
					isDestructive
					isBusy={ isDeleting }
					disabled={ isDeleting || usage.isLoading || usage.hasError }
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
}: {
	item: SpecificationItem;
	sets: string[];
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
			<Notice status="warning" isDismissible={ false }>
				{ VALUES_KEPT }
			</Notice>
		</>
	);
};

const CombinedImpact = ( {
	items,
	usage,
}: {
	items: SpecificationItem[];
	usage: ReturnType< typeof useSetUsage >;
} ) => (
	<>
		<table className="ps-impact-table">
			<thead>
				<tr>
					<th>{ __( 'Specification', 'product-specifications' ) }</th>
					<th>{ __( 'Sets', 'product-specifications' ) }</th>
				</tr>
			</thead>
			<tbody>
				{ items.map( ( item ) => (
					<tr key={ item.id }>
						<td>{ item.name }</td>
						<td>
							{ usage.bySpecification[ item.id ]?.length ?? 0 }
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
				</tr>
			</tbody>
		</table>
		<Notice status="warning" isDismissible={ false }>
			{ VALUES_KEPT_PLURAL }
		</Notice>
	</>
);

export default DeleteDialog;
