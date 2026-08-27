import { TableCard } from '@woocommerce/components';
import type { ReactNode } from 'react';

export interface DataTableHeader {
	key: string;
	label: ReactNode;
	required?: boolean;
	isSortable?: boolean;
	isNumeric?: boolean;
	isLeftAligned?: boolean;
	defaultSort?: boolean;
	defaultOrder?: string;
	cellClassName?: string;
}

export interface DataTableCell {
	display: ReactNode;
	value?: string | number;
}

export interface DataTableProps {
	title: string;
	headers: DataTableHeader[];
	rows: DataTableCell[][];
	rowsPerPage: number;
	totalRows: number;
	isLoading: boolean;
	query: Record< string, unknown >;
	onQueryChange: ( param: string ) => ( ...args: string[] ) => void;
	className?: string;
	showMenu?: boolean;
	// 1-indexed column that labels each row for assistive technology.
	rowHeader?: number;
}

const DataTable = ( props: DataTableProps ) => <TableCard { ...props } />;

export default DataTable;
