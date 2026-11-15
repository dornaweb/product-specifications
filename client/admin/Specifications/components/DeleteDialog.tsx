import { Button, Modal, Notice, Spinner } from '@wordpress/components';
import { __, sprintf, _n } from '@wordpress/i18n';
import { useDeletionImpact } from '../hooks/useDeletionImpact';
import { useSetUsage } from '../hooks/useSetUsage';
import type { SpecificationItem } from '../mapRecord';

interface Props {
	items: SpecificationItem[];
	isDeleting: boolean;
	onConfirm: () => void;
	onCancel: () => void;
}

// Above this many affected products the server finishes the removal in the
// background after the deletion. It mirrors the server's own limit.
const BACKGROUND_ABOVE = 200;

const valuesRemoved = ( products: number ) =>
	products > BACKGROUND_ABOVE
		? sprintf(
				/* translators: %d: number of products holding a value. */
				__(
					'Deleting removes its value from all %d products. That many are removed in the background, and the key stays reserved until it has finished. Deleting cannot be undone.',
					'product-specifications'
				),
				products
		  )
		: __(
				'Deleting removes its value from every product that has one. Deleting cannot be undone.',
				'product-specifications'
		  );

const DeleteDialog = ( { items, isDeleting, onConfirm, onCancel }: Props ) => {
	const usage = useSetUsage( items );
	const impact = useDeletionImpact( items );
	const isLoading = usage.isLoading || impact.isLoading;
	const hasError = usage.hasError || impact.hasError;
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
			{ isLoading && (
				<p>
					<Spinner />{ ' ' }
					{ __(
						'Checking affected sets and products…',
						'product-specifications'
					) }
				</p>
			) }
			{ ! isLoading && hasError && (
				<Notice status="error" isDismissible={ false }>
					{ __(
						'The affected sets and products could not be loaded. Deletion is unavailable until the impact can be checked.',
						'product-specifications'
					) }
				</Notice>
			) }
			{ ! isLoading && ! hasError && isSingle && (
				<SingleImpact
					item={ items[ 0 ] as SpecificationItem }
					sets={ usage.bySpecification[ items[ 0 ]?.id ?? 0 ] ?? [] }
					products={ impact.total }
				/>
			) }
			{ ! isLoading && ! hasError && ! isSingle && (
				<CombinedImpact
					items={ items }
					usage={ usage }
					products={ impact.products }
					total={ impact.total }
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
					disabled={ isDeleting || isLoading || hasError }
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
	products,
}: {
	item: SpecificationItem;
	sets: string[];
	products: number;
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
					{ products === 0
						? __(
								'No product holds a value for it.',
								'product-specifications'
						  )
						: sprintf(
								/* translators: %d: number of products holding a value. */
								_n(
									'%d product holds a value for it.',
									'%d products hold a value for it.',
									products,
									'product-specifications'
								),
								products
						  ) }
				</b>
			</p>
			<Notice status="warning" isDismissible={ false }>
				{ valuesRemoved( products ) }
			</Notice>
		</>
	);
};

const CombinedImpact = ( {
	items,
	usage,
	products,
	total,
}: {
	items: SpecificationItem[];
	usage: ReturnType< typeof useSetUsage >;
	products: Record< number, number >;
	total: number;
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
						<td>{ products[ item.id ] ?? 0 }</td>
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
						<b>
							{ sprintf(
								/* translators: %d: number of product values across the selection. */
								_n(
									'%d value',
									'%d values',
									total,
									'product-specifications'
								),
								total
							) }
						</b>
					</td>
				</tr>
			</tbody>
		</table>
		<Notice status="warning" isDismissible={ false }>
			{ valuesRemoved( total ) }
		</Notice>
	</>
);

export default DeleteDialog;
