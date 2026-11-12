import type { ReactNode } from 'react';

interface Props {
	children: ReactNode;
}

const FieldGrid = ( { children }: Props ) => (
	<div className="ps-field-grid">{ children }</div>
);

export default FieldGrid;
