import type { ReactNode } from 'react';
import { BaseControl, useBaseControlProps } from '@wordpress/components';
import clsx from 'clsx';

export type FieldHelpTone = 'neutral' | 'warning' | 'error';

interface Props {
	label: string;
	children: ( controlProps: Record< string, unknown > ) => ReactNode;
	help?: ReactNode;
	helpTone?: FieldHelpTone;
	hideLabel?: boolean;
	wide?: boolean;
}

// One geometry for every field: label, control, help. BaseControl supplies the
// label and the help element and ties them to the control by id, so a field
// never has to wire that itself.
const Field = ( {
	label,
	children,
	help,
	helpTone = 'neutral',
	hideLabel,
	wide,
}: Props ) => {
	// The tone is carried on an element of ours inside the help slot. Recoloring
	// BaseControl's own help class would reach into markup we do not own.
	const { baseControlProps, controlProps } = useBaseControlProps( {
		label,
		help: help ? (
			<span className={ clsx( 'ps-field__help', `is-${ helpTone }` ) }>
				{ help }
			</span>
		) : undefined,
		hideLabelFromVision: hideLabel,
	} );

	return (
		<div className={ clsx( 'ps-field', { 'ps-field--wide': wide } ) }>
			<BaseControl { ...baseControlProps } __nextHasNoMarginBottom>
				{ children( controlProps as Record< string, unknown > ) }
			</BaseControl>
		</div>
	);
};

export default Field;
