import { SelectControl, TextControl } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { Field, type FieldHelpTone } from '../../ui';
import type { SpecificationTypeOptionsProps } from '../index';
import {
	ATTRIBUTE,
	NAME,
	SOURCE,
	choiceFor,
	isCustomAttribute,
	productDataChoices,
	productDataOptions,
	type ProductDataChoice,
} from './productData';

const CUSTOM_ATTRIBUTE = `${ ATTRIBUTE }:custom`;

const GROUPS: Record< string, string > = {
	shipping: __( 'Shipping', 'product-specifications' ),
	identifiers: __( 'Identifiers', 'product-specifications' ),
	taxonomies: __(
		'Categories, tags and other taxonomies',
		'product-specifications'
	),
	attributes: __( 'Attributes', 'product-specifications' ),
};

const choiceKey = ( choice: { source: string; name: string } ) =>
	choice.name === '' ? choice.source : `${ choice.source }:${ choice.name }`;

const ProductDataOptionsPanel = ( {
	options,
	onChange,
	error,
}: SpecificationTypeOptionsProps ) => {
	const choices = productDataChoices();
	const current = productDataOptions( options );
	const isCustom = isCustomAttribute( current, choices );
	const selected = isCustom ? CUSTOM_ATTRIBUTE : choiceKey( current );
	const isUnavailable =
		current.source !== '' && ! isCustom && ! choiceFor( current, choices );

	let sourceHelp: string | undefined = __(
		'Read from the product on every page view. Nothing is entered on the product screen.',
		'product-specifications'
	);
	let sourceHelpTone: FieldHelpTone = 'neutral';

	if ( current.source === '' ) {
		sourceHelp = error;
		sourceHelpTone = 'error';
	} else if ( isUnavailable ) {
		sourceHelp = __(
			'This product data is not available on this site, so the row stays empty. It returns when the plugin that provides it is active again.',
			'product-specifications'
		);
		sourceHelpTone = 'warning';
	}

	const choose = ( key: string ) => {
		if ( key === CUSTOM_ATTRIBUTE ) {
			onChange( { [ SOURCE ]: ATTRIBUTE, [ NAME ]: '' } );
			return;
		}

		const choice = choices.find( ( item ) => choiceKey( item ) === key );

		if ( choice ) {
			onChange(
				choice.name === ''
					? { [ SOURCE ]: choice.source }
					: { [ SOURCE ]: choice.source, [ NAME ]: choice.name }
			);
		}
	};

	const grouped = Object.keys( GROUPS ).map( ( group ) => ( {
		group,
		items: choices.filter( ( choice ) => choice.group === group ),
	} ) );

	return (
		<>
			<Field
				label={ __( 'Product data', 'product-specifications' ) }
				help={ sourceHelp }
				helpTone={ sourceHelpTone }
			>
				{ ( controlProps ) => (
					<SelectControl
						{ ...controlProps }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						value={ selected }
						onChange={ choose }
					>
						<option value="" disabled>
							{ __(
								'Choose product data',
								'product-specifications'
							) }
						</option>
						{ isUnavailable && (
							<option value={ selected } disabled>
								{ sprintf(
									/* translators: %s: the taxonomy or source id that is no longer available. */
									__(
										'Unavailable: %s',
										'product-specifications'
									),
									current.name || current.source
								) }
							</option>
						) }
						{ grouped.map( ( { group, items } ) => (
							<optgroup key={ group } label={ GROUPS[ group ] }>
								{ items.map( ( item: ProductDataChoice ) => (
									<option
										key={ choiceKey( item ) }
										value={ choiceKey( item ) }
									>
										{ item.label }
									</option>
								) ) }
								{ group === 'attributes' && (
									<option value={ CUSTOM_ATTRIBUTE }>
										{ __(
											'Custom attribute…',
											'product-specifications'
										) }
									</option>
								) }
							</optgroup>
						) ) }
					</SelectControl>
				) }
			</Field>
			{ isCustom && (
				<Field
					label={ __( 'Attribute name', 'product-specifications' ) }
					help={
						current.name === ''
							? error
							: __(
									'As it is named on the product.',
									'product-specifications'
							  )
					}
					helpTone={ current.name === '' ? 'error' : undefined }
				>
					{ ( controlProps ) => (
						<TextControl
							{ ...controlProps }
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							value={
								typeof options[ NAME ] === 'string'
									? options[ NAME ]
									: ''
							}
							onChange={ ( next ) =>
								onChange( {
									[ SOURCE ]: ATTRIBUTE,
									[ NAME ]: next,
								} )
							}
						/>
					) }
				</Field>
			) }
		</>
	);
};

export default ProductDataOptionsPanel;
