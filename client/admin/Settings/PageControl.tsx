import { ComboboxControl } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';

interface PageRecord {
	id: number;
	title: { rendered: string };
}

interface Props {
	label: string;
	pageId: number;
	onChange: ( pageId: number ) => void;
}

const PageControl = ( { label, pageId, onChange }: Props ) => {
	const [ search, setSearch ] = useState( '' );

	const { found, selected } = useSelect(
		( select ) => {
			const core = select( coreStore );

			return {
				found: core.getEntityRecords( 'postType', 'page', {
					search,
					per_page: 20,
					status: 'publish',
				} ) as PageRecord[] | null,
				selected:
					pageId > 0
						? ( core.getEntityRecord(
								'postType',
								'page',
								pageId
						  ) as PageRecord | undefined )
						: undefined,
			};
		},
		[ search, pageId ]
	);

	const matches = found ?? [];
	const pages = selected
		? [ selected, ...matches.filter( ( page ) => page.id !== selected.id ) ]
		: matches;

	return (
		<ComboboxControl
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			label={ label }
			hideLabelFromVision
			value={ pageId > 0 ? String( pageId ) : null }
			options={ pages.map( ( page ) => ( {
				value: String( page.id ),
				label: decodeEntities( page.title.rendered ),
			} ) ) }
			onFilterValueChange={ setSearch }
			onChange={ ( value ) => onChange( value ? Number( value ) : 0 ) }
		/>
	);
};

export default PageControl;
