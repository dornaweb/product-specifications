import { __ } from '@wordpress/i18n';
import {
	SelectControl,
	TextareaControl,
	TextControl,
} from '@wordpress/components';
import {
	type SpecificationSetGeneralState,
	type SpecificationSetStatus,
} from '../state';

interface GeneralTabProps {
	general: SpecificationSetGeneralState;
	updateTextField: (
		field: keyof Omit< SpecificationSetGeneralState, 'status' >
	) => ( value: string | undefined ) => void;
	updateStatus: ( status: SpecificationSetStatus | undefined ) => void;
}

const GeneralTab = ( {
	general,
	updateTextField,
	updateStatus,
}: GeneralTabProps ) => (
	<div className="product-specifications-specs-set-editor__general">
		<TextControl
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			label={ __( 'Title', 'product-specifications' ) }
			help={ __(
				'Display name for this specification set.',
				'product-specifications'
			) }
			value={ general.title }
			onChange={ updateTextField( 'title' ) }
			required
		/>

		<TextControl
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			label={ __( 'Key / Slug', 'product-specifications' ) }
			help={ __(
				'Machine-readable identifier using lowercase letters, numbers, underscores, or hyphens.',
				'product-specifications'
			) }
			value={ general.slug }
			onChange={ updateTextField( 'slug' ) }
		/>

		<TextareaControl
			__nextHasNoMarginBottom
			label={ __( 'Description', 'product-specifications' ) }
			value={ general.description }
			onChange={ updateTextField( 'description' ) }
			rows={ 4 }
		/>

		<SelectControl
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			label={ __( 'Status', 'product-specifications' ) }
			value={ general.status }
			options={ [
				{
					label: __( 'Publish', 'product-specifications' ),
					value: 'publish',
				},
				{
					label: __( 'Draft', 'product-specifications' ),
					value: 'draft',
				},
			] }
			onChange={ ( value ) =>
				updateStatus( value as SpecificationSetStatus | undefined )
			}
		/>
	</div>
);

export default GeneralTab;
