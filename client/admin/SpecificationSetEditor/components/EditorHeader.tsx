import { __ } from '@wordpress/i18n';
import { Button, Flex, FlexItem } from '@wordpress/components';
import type { SpecificationSetEditorMode } from '../hooks/useSpecificationSetEditor';

interface EditorHeaderProps {
	mode: SpecificationSetEditorMode;
	hasUnsavedChanges: boolean;
	isSaving: boolean;
}

const EditorHeader = ( {
	mode,
	hasUnsavedChanges,
	isSaving,
}: EditorHeaderProps ) => (
	<Flex className="product-specifications-specs-sets__header">
		<FlexItem>
			<h2>
				{ mode === 'edit'
					? __( 'Edit Specification Set', 'product-specifications' )
					: __(
							'Add new Specification Set',
							'product-specifications'
					  ) }
			</h2>
			<p>
				{ __(
					'Group your specifications into a reusable set for products.',
					'product-specifications'
				) }
			</p>
		</FlexItem>

		<FlexItem>
			<Flex align="center">
				<Button
					variant="secondary"
					className="product-specifications-specs-sets__cancel-button"
					type="button"
				>
					{ __( 'Cancel', 'product-specifications' ) }
				</Button>
				<Button
					variant="primary"
					className="product-specifications-specs-sets__create-button"
					type="submit"
					disabled={ ! hasUnsavedChanges || isSaving }
					isBusy={ isSaving }
				>
					{ isSaving
						? __( 'Saving…', 'product-specifications' )
						: __(
								'Save Specification Set',
								'product-specifications'
						  ) }
				</Button>
			</Flex>
		</FlexItem>
	</Flex>
);

export default EditorHeader;
