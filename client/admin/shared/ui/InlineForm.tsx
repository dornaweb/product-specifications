import type { ReactNode } from 'react';
import clsx from 'clsx';

interface Props {
	label: string;
	children: ReactNode;
	actions?: ReactNode;
	atTop?: boolean;
}

// An editing form opens in the list rather than over it. A form that opens
// above the first row keeps its position as rows are added beneath it.
const InlineForm = ( { label, children, actions, atTop }: Props ) => (
	<div className={ clsx( 'ps-inline-form', { 'is-at-top': atTop } ) }>
		<div className="ps-inline-form__label">{ label }</div>
		{ children }
		{ actions && (
			<div className="ps-inline-form__actions">{ actions }</div>
		) }
	</div>
);

export default InlineForm;
