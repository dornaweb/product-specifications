import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import SpecificationForm, {
	emptyDraft,
	type SpecificationDraft,
} from '../../Specifications/components/SpecificationForm';
import { payloadFrom } from '../../Specifications/payload';
import { useCreateSpecification } from '../../Specifications/hooks/useCreateSpecification';
import type { LibraryEntry } from '../hooks/useSpecificationLibrary';

interface Props {
	onCreated: ( entry: LibraryEntry ) => void;
	onCancel: () => void;
}

// The library screen's form, opened where the set is being composed. On save the
// specification joins the set, not only the library: using it here was the
// intent.
const NewSpecificationForm = ( { onCreated, onCancel }: Props ) => {
	const [ draft, setDraft ] = useState< SpecificationDraft >( emptyDraft() );
	const { create, isSaving, error } = useCreateSpecification();

	return (
		<SpecificationForm
			draft={ draft }
			label={ __( 'New specification', 'product-specifications' ) }
			isSaving={ isSaving }
			error={ error }
			onChange={ setDraft }
			onCancel={ onCancel }
			saveLabel={ __( 'Create and add', 'product-specifications' ) }
			onSaveAndClose={ async () => {
				const saved = await create( payloadFrom( draft ) );

				if ( saved ) {
					onCreated( { ...saved, isInSet: false } );
				}
			} }
		/>
	);
};

export default NewSpecificationForm;
