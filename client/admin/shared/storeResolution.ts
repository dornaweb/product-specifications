// `@wordpress/data` mixes resolution control into every store that has
// resolvers. The store packages do not declare it on their own selector and
// action types, so it is described here and asserted at the call site.

export interface ResolutionSelectors {
	isResolving: ( selectorName: string, args: unknown[] ) => boolean;
	hasFinishedResolution: ( selectorName: string, args: unknown[] ) => boolean;
	hasResolutionFailed: ( selectorName: string, args: unknown[] ) => boolean;
	getResolutionError: ( selectorName: string, args: unknown[] ) => unknown;
}

export interface ResolutionActions {
	invalidateResolution: ( selectorName: string, args: unknown[] ) => void;
	invalidateResolutionForStoreSelector: (
		selectorName: string
	) => Promise< void >;
}
