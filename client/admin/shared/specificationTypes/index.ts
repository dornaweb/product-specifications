import { applyFilters } from '@wordpress/hooks';
import { __, sprintf } from '@wordpress/i18n';
import type { ComponentType } from 'react';
import { coreSpecificationTypes } from './coreTypes';

export const TYPES_FILTER = 'product_specifications.types';

export interface SpecificationTypeDefaultProps {
	value: unknown;
	options: Record< string, unknown >;
	onChange: ( value: unknown ) => void;
}

export interface SpecificationTypeValueProps {
	label: string;
	value: unknown;
	options: Record< string, unknown >;
	onChange: ( value: unknown ) => void;
}

export interface SpecificationTypeOptionsProps {
	options: Record< string, unknown >;
	onChange: ( options: Record< string, unknown > ) => void;
	error?: string;
}

export interface SpecificationTypeDefinition {
	label: string;
	description: string;
	options?: ComponentType< SpecificationTypeOptionsProps >;
	// The control that authors the type's default. A type without one takes no
	// default, rather than falling back to a text box whose string the type
	// would reject on save.
	defaultControl?: ComponentType< SpecificationTypeDefaultProps >;
	// Product values have their own explicit empty state. Resolution still
	// happens through this registry so extensions use the same seam as core.
	valueControl?: ComponentType< SpecificationTypeValueProps >;
	// The value is read from the product rather than entered. The product
	// screen shows valueControl as information only, suggests no default, and
	// does not count the specification as one left to fill.
	readOnly?: boolean;
	// Returns the message to show on the options field, or undefined when the
	// configuration is complete. The server rejects an incomplete one, and a
	// rejection that arrives after the save has left the field is not the
	// failure the editor can act on.
	validateOptions?: (
		options: Record< string, unknown >
	) => string | undefined;
}

export interface ResolvedSpecificationType extends SpecificationTypeDefinition {
	id: string;
	isRegistered: boolean;
}

export type SpecificationTypes = Record< string, SpecificationTypeDefinition >;

export function specificationTypes(): SpecificationTypes {
	return applyFilters(
		TYPES_FILTER,
		coreSpecificationTypes
	) as SpecificationTypes;
}

// Nothing enforces that a server type has a client half, so an unregistered id
// resolves to a descriptor the editor can name rather than to undefined.
export function specificationType( id: string ): ResolvedSpecificationType {
	const registered = specificationTypes()[ id ];

	if ( registered ) {
		return { ...registered, id, isRegistered: true };
	}

	return {
		id,
		isRegistered: false,
		description: '',
		label: sprintf(
			/* translators: %s: the unregistered specification type id. */
			__( 'Unregistered type: %s', 'product-specifications' ),
			id
		),
	};
}
