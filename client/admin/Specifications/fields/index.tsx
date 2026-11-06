import { __, sprintf, _n } from '@wordpress/i18n';
import type { Field, View } from '@wordpress/dataviews';
import { defaultListView } from '../../shared/ui/listView';
import { specificationTypes } from '../../shared/specificationTypes';
import type { SpecificationItem } from '../mapRecord';

export function typeElements() {
	return Object.entries( specificationTypes() ).map( ( [ id, type ] ) => ( {
		value: id,
		label: type.label,
	} ) );
}

export function fields(): Field< SpecificationItem >[] {
	return [
		{
			id: 'name',
			label: __( 'Specification', 'product-specifications' ),
			enableGlobalSearch: true,
			enableSorting: true,
			render: ( { item }: { item: SpecificationItem } ) => (
				<div className="ps-spec-name">
					<span className="ps-spec-name__label">
						{ item.name }
						{ item.isJustAdded && (
							<span className="ps-pill is-success">
								{ __( 'just added', 'product-specifications' ) }
							</span>
						) }
					</span>
					<span className="ps-spec-name__slug">{ item.slug }</span>
				</div>
			),
		},
		{
			id: 'type',
			label: __( 'Type', 'product-specifications' ),
			enableSorting: false,
			elements: typeElements(),
			filterBy: { operators: [ 'is' as const ] },
			getValue: ( { item }: { item: SpecificationItem } ) => item.type,
			render: ( { item }: { item: SpecificationItem } ) =>
				item.typeLabel || item.type,
		},
		{
			id: 'usageCount',
			label: __( 'Sets', 'product-specifications' ),
			enableSorting: false,
			render: ( { item }: { item: SpecificationItem } ) =>
				item.usageCount === 0 ? (
					<span className="ps-pill is-warning">
						{ __(
							'Not used in any set',
							'product-specifications'
						) }
					</span>
				) : (
					<span>
						{ sprintf(
							/* translators: %d: number of sets using this specification. */
							_n(
								'%d set',
								'%d sets',
								item.usageCount,
								'product-specifications'
							),
							item.usageCount
						) }
					</span>
				),
		},
	];
}

export const defaultView: View = defaultListView( {
	sort: { field: 'name', direction: 'asc' },
	titleField: 'name',
	fields: [ 'type', 'usageCount' ],
} );
