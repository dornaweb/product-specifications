import {
	getContext,
	getElement,
	store,
	withSyncEvent,
} from '@wordpress/interactivity';

interface Result {
	id: number;
	name: string;
	image: string | null;
}

type Status = 'idle' | 'loading' | 'done' | 'failed';

interface Context {
	set: string;
	ids: string;
	pageUrl: string;
	isOpen: boolean;
	query: string;
	results: Result[];
	activeIndex: number;
	status: Status;
	restoreFocus: boolean;
	result: Result;
}

interface State {
	searchUrl: string;
	searching: string;
	noMatches: string;
	failed: string;
	found: string;
	readonly hasResults: boolean;
	readonly hasFailed: boolean;
	readonly activeId: string;
	readonly optionId: string;
	readonly isActive: boolean;
	readonly statusText: string;
}

const SEARCH_DELAY = 250;

let latestRequest = 0;

const optionIdFor = ( result: Result ): string =>
	`ps-comparison-option-${ result.id }`;

const comparisonUrl = ( context: Context, id: number ): string => {
	const url = new URL( context.pageUrl );
	const ids = [ ...context.ids.split( ',' ).filter( Boolean ), String( id ) ];

	url.searchParams.set( 'compare', ids.join( ',' ) );

	return url.toString().replace( /%2C/g, ',' );
};

const searchUrl = ( searchBase: string, context: Context ): string => {
	const url = new URL( searchBase, window.location.href );

	url.searchParams.set( 'set', context.set );
	url.searchParams.set( 'search', context.query.trim() );
	url.searchParams.set( 'exclude', context.ids );

	return url.toString();
};

const collapse = ( context: Context ): void => {
	++latestRequest;
	context.isOpen = false;
	context.query = '';
	context.results = [];
	context.activeIndex = -1;
	context.status = 'idle';
};

function* fetchResults(
	context: Context
): Generator< unknown, void, unknown > {
	const request = ++latestRequest;

	context.activeIndex = -1;

	if ( context.query.trim() === '' ) {
		context.results = [];
		context.status = 'idle';
		return;
	}

	yield new Promise( ( resolve ) => setTimeout( resolve, SEARCH_DELAY ) );

	if ( request !== latestRequest ) {
		return;
	}

	context.status = 'loading';

	try {
		const response = ( yield fetch(
			searchUrl( state.searchUrl, context )
		) ) as Response;

		if ( ! response.ok ) {
			throw new Error( response.statusText );
		}

		const results = ( yield response.json() ) as Result[];

		if ( request === latestRequest ) {
			context.activeIndex = -1;
			context.results = results;
			context.status = 'done';
		}
	} catch {
		if ( request === latestRequest ) {
			context.results = [];
			context.status = 'failed';
		}
	}
}

const { state, actions } = store( 'product-specifications/comparison', {
	state: {
		get hasResults(): boolean {
			return getContext< Context >().results.length > 0;
		},
		get hasFailed(): boolean {
			return getContext< Context >().status === 'failed';
		},
		get activeId(): string {
			const context = getContext< Context >();
			const active = context.results[ context.activeIndex ];

			return active ? optionIdFor( active ) : '';
		},
		get optionId(): string {
			return optionIdFor( getContext< Context >().result );
		},
		get isActive(): boolean {
			const context = getContext< Context >();

			return (
				context.results[ context.activeIndex ]?.id === context.result.id
			);
		},
		get statusText(): string {
			const context = getContext< Context >();

			switch ( context.status ) {
				case 'loading':
					return state.searching;
				case 'failed':
					return state.failed;
				case 'done':
					return context.results.length > 0
						? state.found.replace(
								/%(1\$)?d/,
								String( context.results.length )
						  )
						: state.noMatches;
				default:
					return '';
			}
		},
	} as State,
	actions: {
		open(): void {
			getContext< Context >().isOpen = true;
		},
		close(): void {
			const context = getContext< Context >();

			collapse( context );
			context.restoreFocus = true;
		},
		leave: withSyncEvent( ( event: FocusEvent ): void => {
			const picker = event.currentTarget as HTMLElement;
			const context = getContext< Context >();

			if (
				context.isOpen &&
				! picker.contains( event.relatedTarget as Node | null )
			) {
				collapse( context );
			}
		} ),
		keepFocus: withSyncEvent( ( event: MouseEvent ): void => {
			event.preventDefault();
		} ),
		*search( event: Event ): Generator< unknown, void, unknown > {
			const context = getContext< Context >();

			context.query = ( event.target as HTMLInputElement ).value;
			yield* fetchResults( context );
		},
		*retry(): Generator< unknown, void, unknown > {
			( getElement().ref as HTMLElement | null )
				?.closest( '.ps-comparison__picker' )
				?.querySelector< HTMLInputElement >( '.ps-comparison__input' )
				?.focus();

			yield* fetchResults( getContext< Context >() );
		},
		navigate: withSyncEvent( ( event: KeyboardEvent ): void => {
			const context = getContext< Context >();
			const count = context.results.length;
			const active = context.results[ context.activeIndex ];

			if ( event.key === 'ArrowDown' && count > 0 ) {
				event.preventDefault();
				context.activeIndex = ( context.activeIndex + 1 ) % count;
			} else if ( event.key === 'ArrowUp' && count > 0 ) {
				event.preventDefault();
				context.activeIndex =
					( context.activeIndex - 1 + count ) % count;
			} else if ( event.key === 'Enter' && active ) {
				event.preventDefault();
				window.location.assign( comparisonUrl( context, active.id ) );
			} else if ( event.key === 'Escape' ) {
				event.preventDefault();
				actions.close();
			}
		} ),
		pick(): void {
			const context = getContext< Context >();

			window.location.assign(
				comparisonUrl( context, context.result.id )
			);
		},
	},
	callbacks: {
		focusWhenOpen(): void {
			if ( getContext< Context >().isOpen ) {
				( getElement().ref as HTMLInputElement | null )?.focus();
			}
		},
		focusWhenClosed(): void {
			const context = getContext< Context >();

			if ( ! context.isOpen && context.restoreFocus ) {
				context.restoreFocus = false;
				( getElement().ref as HTMLButtonElement | null )?.focus();
			}
		},
	},
} );
