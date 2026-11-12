export const getNewPath = ( query: Record< string, unknown >, path: string ) =>
	`${ path }?${ new URLSearchParams(
		Object.entries( query ).map( ( [ key, value ] ) => [
			key,
			String( value ),
		] )
	).toString() }`;
export const getPath = () => '/';
export const navigateTo = () => undefined;
export const useQuery = () => ( {} );
