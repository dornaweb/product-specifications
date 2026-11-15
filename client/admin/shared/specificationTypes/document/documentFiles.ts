export interface StoredFile {
	id: number;
	label?: string;
}

export function storedFiles( value: unknown ): StoredFile[] {
	if ( ! Array.isArray( value ) ) {
		return [];
	}

	return value.flatMap( ( item ): StoredFile[] => {
		const id = Number( item?.id );

		if ( ! Number.isInteger( id ) || id < 1 ) {
			return [];
		}

		return typeof item.label === 'string' && item.label !== ''
			? [ { id, label: item.label } ]
			: [ { id } ];
	} );
}

export function withPicked( files: StoredFile[], ids: number[] ): StoredFile[] {
	const listed = new Set( files.map( ( file ) => file.id ) );

	return [
		...files,
		...ids
			.filter(
				( id, index ) =>
					! listed.has( id ) && ids.indexOf( id ) === index
			)
			.map( ( id ) => ( { id } ) ),
	];
}

export function withLabel(
	files: StoredFile[],
	id: number,
	label: string
): StoredFile[] {
	const labeled: StoredFile = label === '' ? { id } : { id, label };

	return files.map( ( file ) => ( file.id === id ? labeled : file ) );
}

export const asValue = ( files: StoredFile[] ) =>
	files.length > 0 ? files : null;
