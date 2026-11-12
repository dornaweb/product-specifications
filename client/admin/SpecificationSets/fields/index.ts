import { __, sprintf, _n } from '@wordpress/i18n';
import type { Action, Field, View } from '@wordpress/dataviews';
import { defaultListView } from '../../shared/ui/listView';
import { adminPath, goToAdminPath } from '../../shared/platform';
import { EDIT_SPECIFICATION_SET_PATH } from '../../shared/paths';

export type SpecificationSetStatus = 'publish' | 'draft';

export interface SpecificationSetItem {
	id: number;
	title: string;
	status: SpecificationSetStatus;
	description: string;
	structure: unknown;
	specifications: number;
	groups: number;
	products: number;
}

export const STATUS_LABELS: Record< SpecificationSetStatus, string > = {
	publish: __( 'Published', 'product-specifications' ),
	draft: __( 'Draft', 'product-specifications' ),
};

// The DataViews field and action definitions for the sets list. Kept apart from
// the screen because this is the part with mapping in it: a REST record becomes
// a row, and a row becomes a destination.
export const fields: Field< SpecificationSetItem >[] = [
	{
		id: 'title',
		label: __( 'Set name', 'product-specifications' ),
		enableGlobalSearch: true,
		enableSorting: true,
	},
	{
		id: 'status',
		label: __( 'Status', 'product-specifications' ),
		enableSorting: false,
		elements: [
			{ value: 'publish', label: STATUS_LABELS.publish },
			{ value: 'draft', label: STATUS_LABELS.draft },
		],
		filterBy: { operators: [ 'is' as const ] },
		getValue: ( { item }: { item: SpecificationSetItem } ) => item.status,
		render: ( { item }: { item: SpecificationSetItem } ) =>
			STATUS_LABELS[ item.status ],
	},
	// Both counts are derived from the structure document after the records
	// arrive, so the REST collection cannot order by them. Offering a sort
	// control here would silently reorder by date instead.
	{
		id: 'specifications',
		label: __( 'Specifications', 'product-specifications' ),
		enableSorting: false,
	},
	{
		id: 'groups',
		label: __( 'Groups', 'product-specifications' ),
		enableSorting: false,
	},
	// A set's usage is one indexed meta lookup, so it is counted here. The
	// products holding a value for a single specification are a scan, and are
	// counted nowhere.
	{
		id: 'products',
		label: __( 'Products', 'product-specifications' ),
		enableSorting: false,
		render: ( { item }: { item: SpecificationSetItem } ) =>
			sprintf(
				/* translators: %d: number of products the set is assigned to. */
				_n(
					'%d product',
					'%d products',
					item.products,
					'product-specifications'
				),
				item.products
			),
	},
];

export const defaultView: View = defaultListView( {
	sort: { field: 'title', direction: 'asc' },
	titleField: 'title',
	fields: [ 'status', 'specifications', 'groups', 'products' ],
} );

export function editUrl( id: number ): string {
	return adminPath( EDIT_SPECIFICATION_SET_PATH, { id } );
}

export interface SetActionHandlers {
	duplicate: ( item: SpecificationSetItem ) => Promise< void >;
	remove: ( items: SpecificationSetItem[] ) => Promise< void >;
	setStatus: (
		items: SpecificationSetItem[],
		status: SpecificationSetStatus
	) => Promise< void >;
}

export function createActions(
	handlers: SetActionHandlers
): Action< SpecificationSetItem >[] {
	return [
		{
			id: 'edit',
			label: __( 'Edit', 'product-specifications' ),
			isPrimary: true,
			callback: ( items: SpecificationSetItem[] ) => {
				const [ item ] = items;

				if ( item ) {
					goToAdminPath( EDIT_SPECIFICATION_SET_PATH, {
						id: item.id,
					} );
				}
			},
		},
		{
			id: 'duplicate',
			label: __( 'Duplicate', 'product-specifications' ),
			supportsBulk: false,
			callback: ( items: SpecificationSetItem[] ) => {
				const [ item ] = items;

				if ( item ) {
					handlers.duplicate( item );
				}
			},
		},
		{
			id: 'publish',
			label: __( 'Publish', 'product-specifications' ),
			supportsBulk: true,
			isEligible: ( item: SpecificationSetItem ) =>
				item.status !== 'publish',
			callback: ( items: SpecificationSetItem[] ) =>
				handlers.setStatus( items, 'publish' ),
		},
		{
			id: 'draft',
			label: __( 'Switch to draft', 'product-specifications' ),
			supportsBulk: true,
			isEligible: ( item: SpecificationSetItem ) =>
				item.status !== 'draft',
			callback: ( items: SpecificationSetItem[] ) =>
				handlers.setStatus( items, 'draft' ),
		},
		{
			id: 'delete',
			label: __( 'Delete', 'product-specifications' ),
			supportsBulk: true,
			callback: ( items: SpecificationSetItem[] ) =>
				handlers.remove( items ),
		},
	];
}
