import type { View } from '@wordpress/dataviews';

const DEFAULT_PER_PAGE = 20;

export function defaultListView( overrides: Partial< View > = {} ): View {
	return {
		type: 'table',
		page: 1,
		perPage: DEFAULT_PER_PAGE,
		search: '',
		filters: [],
		...overrides,
	} as View;
}

// A search, a filter or a later page can empty a list that is not itself empty.
// Telling the two apart decides whether the list surface may be replaced:
// replacing a narrowed list leaves no control to undo the narrowing.
export function isNarrowedView( view: View ): boolean {
	return (
		Boolean( view.search ) ||
		( view.filters?.length ?? 0 ) > 0 ||
		( view.page ?? 1 ) > 1
	);
}
