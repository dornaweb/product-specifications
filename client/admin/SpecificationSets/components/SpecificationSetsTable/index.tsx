import { __ } from '@wordpress/i18n';
import { Button, CheckboxControl } from '@wordpress/components';
import TableCard from '@woocommerce/components/build/table';

import './styles.scss';

interface SpecificationSetRecord {
	id: number;
	title: { rendered: string };
	date: string;
}

interface Props {
	records: SpecificationSetRecord[];
	total: number;
	isLoading: boolean;
	query: {
		page: number;
		per_page: number;
		orderby: string;
		order: 'asc' | 'desc';
	};
	onQueryChange: ( param: string ) => ( value: string, direction?: string ) => void;
	selectedIds: number[];
	onSelectRow: ( id: number, checked: boolean ) => void;
	onSelectAll: ( checked: boolean ) => void;
}

const headers = [
	{
		key: 'select',
		label: '',
		required: true,
		isSortable: false,
		isLeftAligned: true,
		cellClassName: 'product-specifications-specs-sets-table__select-col',
	},
	{
		key: 'id',
		label: __( 'ID', 'product-specifications' ),
		isSortable: true,
		isNumeric: true,
	},
	{
		key: 'title',
		label: __( 'Title', 'product-specifications' ),
		isSortable: true,
		defaultSort: true,
		defaultOrder: 'asc',
		isLeftAligned: true,
	},
	{
		key: 'date',
		label: __( 'Date', 'product-specifications' ),
		isSortable: true,
		isLeftAligned: true,
	},
	{
		key: 'actions',
		label: '',
		required: true,
		isSortable: false,
		isLeftAligned: true,
		cellClassName: 'product-specifications-specs-sets-table__actions-col',
	},
];

const SpecificationSetsTable = ( {
	records,
	total,
	isLoading,
	query,
	onQueryChange,
	selectedIds,
	onSelectRow,
	onSelectAll,
}: Props ) => {
	const allSelected =
		records.length > 0 && selectedIds.length === records.length;

	const tableHeaders = headers.map( ( header ) => {
		if ( header.key === 'select' ) {
			return {
				...header,
				label: (
					<CheckboxControl
						__nextHasNoMarginBottom
						checked={ allSelected }
						indeterminate={
							selectedIds.length > 0 && ! allSelected
						}
						onChange={ onSelectAll }
						label=""
						aria-label={ __(
							'Select all',
							'product-specifications'
						) }
					/>
				),
			};
		}
		return header;
	} );

	const rows = records.map( ( record ) => {
		const date = record.date
			? new Date( record.date ).toLocaleDateString()
			: '—';

		return [
			{
				display: (
					<CheckboxControl
						__nextHasNoMarginBottom
						checked={ selectedIds.includes( record.id ) }
						onChange={ ( checked ) =>
							onSelectRow( record.id, checked )
						}
						label=""
						aria-label={ __(
							'Select row',
							'product-specifications'
						) }
					/>
				),
				value: record.id,
			},
			{
				display: record.id,
				value: record.id,
			},
			{
				display: record.title.rendered || __( '(no title)', 'product-specifications' ),
				value: record.title.rendered,
			},
			{
				display: date,
				value: record.date,
			},
			{
				display: (
					<span className="product-specifications-specs-sets-table__row-actions">
						<Button variant="tertiary" size="small">
							{ __( 'Edit', 'product-specifications' ) }
						</Button>
						<Button
							variant="tertiary"
							size="small"
							isDestructive
						>
							{ __( 'Delete', 'product-specifications' ) }
						</Button>
					</span>
				),
				value: '',
			},
		];
	} );

	return (
		<TableCard
			className="product-specifications-specs-sets-table"
			title={ __( 'Specification Sets', 'product-specifications' ) }
			headers={ tableHeaders }
			rows={ rows }
			rowsPerPage={ query.per_page }
			totalRows={ total }
			isLoading={ isLoading }
			query={ { paged: String( query.page ), orderby: query.orderby, order: query.order } }
			onQueryChange={ onQueryChange }
			rowHeader={ 2 }
			showMenu
		/>
	);
};

export default SpecificationSetsTable;
