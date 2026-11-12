import type { SpecificationDraft } from './components/SpecificationForm';

// The shape the REST field expects, from the shape the form holds. Both screens
// that create a specification send this.
export function payloadFrom( draft: SpecificationDraft ) {
	return {
		name: draft.name.trim(),
		description: draft.description,
		specification: {
			type: draft.type,
			options: draft.options,
			default: draft.defaultValue,
		},
	};
}
