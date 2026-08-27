import { Placeholder, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import Layout from '../shared/layout';
import EditorHeader from './components/EditorHeader';
import EditorNotice from './components/EditorNotice';
import EditorTabs from './components/EditorTabs';
import { useSpecificationSetEditor } from './hooks/useSpecificationSetEditor';

import './styles.scss';

const SpecificationSetEditor = () => {
	const editor = useSpecificationSetEditor();

	const onSubmit = async ( event: React.FormEvent< HTMLFormElement > ) => {
		event.preventDefault();
		await editor.save();
	};

	return (
		<Layout title="Specification Sets">
			<form onSubmit={ onSubmit }>
				<div className="product-specifications-specs-set-editor">
					<EditorHeader
						mode={ editor.mode }
						hasUnsavedChanges={ editor.hasUnsavedChanges }
						isSaving={ editor.isSaving }
					/>
					<EditorNotice
						notice={ editor.notice }
						onDismiss={ editor.dismissNotice }
					/>
					{ editor.isLoading ? (
						<Placeholder>
							<Spinner />
							{ __(
								'Loading specification set…',
								'product-specifications'
							) }
						</Placeholder>
					) : editor.loadErrorMessage ? (
						<Placeholder>
							{ editor.loadErrorMessage }
						</Placeholder>
					) : (
						<EditorTabs editor={ editor } />
					) }
				</div>
			</form>
		</Layout>
	);
};

export default SpecificationSetEditor;
