import { ComboboxControl } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';

interface ProductRecord {
	id: number;
	title: { rendered: string };
}

interface Props {
	productId?: number;
	onChange: ( productId?: number ) => void;
}

const ProductControl = ( { productId, onChange }: Props ) => {
	const [ search, setSearch ] = useState( '' );

	const { found, selected } = useSelect(
		( select ) => {
			const core = select( coreStore );

			return {
				found: core.getEntityRecords( 'postType', 'product', {
					search,
					per_page: 20,
				} ) as ProductRecord[] | null,
				selected: productId
					? ( core.getEntityRecord(
							'postType',
							'product',
							productId
					  ) as ProductRecord | undefined )
					: undefined,
			};
		},
		[ search, productId ]
	);

	const matches = found ?? [];
	const products = selected
		? [ selected, ...matches.filter( ( item ) => item.id !== selected.id ) ]
		: matches;

	return (
		<ComboboxControl
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			label={ __( 'Product', 'product-specifications' ) }
			help={ __(
				'Leave empty to show the product being viewed.',
				'product-specifications'
			) }
			value={ productId ? String( productId ) : null }
			options={ products.map( ( product ) => ( {
				value: String( product.id ),
				label: decodeEntities( product.title.rendered ),
			} ) ) }
			onFilterValueChange={ setSearch }
			onChange={ ( value ) =>
				onChange( value ? Number( value ) : undefined )
			}
		/>
	);
};

export default ProductControl;
