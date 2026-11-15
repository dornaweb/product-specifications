import type { SpecificationDraft } from './components/SpecificationForm';

// The shape the REST field expects, from the shape the form holds. Both screens
// that create a specification send this. The slug is sent on a create and is
// stripped by the server on an update, so it never changes afterward.
export function payloadFrom( draft: SpecificationDraft ) {
	return {
		name: draft.name.trim(),
		...( draft.slug ? { slug: draft.slug } : {} ),
		description: draft.description,
		specification: {
			type: draft.type,
			options: draft.options,
			default: draft.defaultValue,
		},
	};
}
