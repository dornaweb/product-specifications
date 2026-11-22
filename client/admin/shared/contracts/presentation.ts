export const PRESENTATION_META_KEY = 'specification_set_presentation';
export const PRESENTATION_SCHEMA_VERSION = 1;

type Emptiness = 'hide' | 'show';

export interface SpecificationSetPresentation {
	schema_version: number;
	empty_rows: Emptiness;
	empty_groups: Emptiness;
	comparison: boolean;
}

export function presentationForNewSet(): SpecificationSetPresentation {
	return { ...readPresentation( null ), comparison: true };
}

export function readPresentation(
	value: unknown
): SpecificationSetPresentation {
	const stored =
		value && typeof value === 'object'
			? ( value as Record< string, unknown > )
			: {};

	return {
		schema_version: PRESENTATION_SCHEMA_VERSION,
		empty_rows: emptiness( stored.empty_rows ),
		empty_groups: emptiness( stored.empty_groups ),
		comparison: stored.comparison === true,
	};
}

function emptiness( value: unknown ): Emptiness {
	return value === 'show' ? 'show' : 'hide';
}
