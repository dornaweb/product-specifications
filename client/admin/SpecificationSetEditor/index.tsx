import { Placeholder, Spinner, Button, Modal } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { displayShortcut } from '@wordpress/keycodes';
import { PageShell, PageHeader, Notice } from '../shared/ui';
import {
	discardUnsavedChangesAndGoToAdminPath,
	goToAdminPath,
} from '../shared/platform';
import EditorTabs from './components/EditorTabs';
import { useSpecificationSetEditor } from './hooks/useSpecificationSetEditor';

import './styles.scss';
import { SPECIFICATION_SETS_PATH } from '../shared/paths';

type Editor = ReturnType< typeof useSpecificationSetEditor >;

const EditorBody = ( { editor }: { editor: Editor } ) => {
	if ( editor.isLoading ) {
		return (
			<Placeholder>
				<Spinner />
				{ __( 'Loading specification set…', 'product-specifications' ) }
			</Placeholder>
		);
	}

	if ( editor.loadErrorMessage ) {
		return <Placeholder>{ editor.loadErrorMessage }</Placeholder>;
	}

	return <EditorTabs editor={ editor } />;
};

const SpecificationSetEditor = () => {
	const editor = useSpecificationSetEditor();
	const [ pendingNavigation, setPendingNavigation ] = useState<
		string | null
	>( null );

	const requestNavigation = ( path: string ) => {
		if ( editor.hasUnsavedChanges ) {
			setPendingNavigation( path );
			return;
		}

		goToAdminPath( path );
	};

	const onSubmit = async ( event: React.FormEvent< HTMLFormElement > ) => {
		event.preventDefault();
		await editor.save();
	};

	const title =
		editor.mode === 'edit'
			? editor.general.title ||
			  __( 'Edit specification set', 'product-specifications' )
			: __( 'New specification set', 'product-specifications' );

	return (
		<form onSubmit={ onSubmit }>
			{ pendingNavigation && (
				<Modal
					title={ __(
						'Leave without saving?',
						'product-specifications'
					) }
					onRequestClose={ () => setPendingNavigation( null ) }
				>
					<p>
						{ __(
							'You have unsaved changes to this specification set. Leaving now discards them.',
							'product-specifications'
						) }
					</p>
					<div className="product-specifications-specs-set-editor__unsaved-actions">
						<Button
							variant="tertiary"
							isDestructive
							onClick={ () =>
								discardUnsavedChangesAndGoToAdminPath(
									pendingNavigation
								)
							}
						>
							{ __(
								'Leave and discard',
								'product-specifications'
							) }
						</Button>
						<Button
							variant="primary"
							onClick={ () => setPendingNavigation( null ) }
						>
							{ __(
								'Stay and keep editing',
								'product-specifications'
							) }
						</Button>
					</div>
				</Modal>
			) }
			<PageShell
				header={
					<PageHeader
						title={ title }
						description={ __(
							'Arrange reusable specifications into the groups customers see on product pages.',
							'product-specifications'
						) }
						breadcrumb={ {
							label: __(
								'Specification sets',
								'product-specifications'
							),
							path: SPECIFICATION_SETS_PATH,
							onNavigate: () =>
								requestNavigation( SPECIFICATION_SETS_PATH ),
						} }
						status={
							editor.hasUnsavedChanges ? (
								<span className="ps-dirty">
									{ __(
										'Unsaved changes',
										'product-specifications'
									) }
								</span>
							) : null
						}
						actions={
							<>
								<Button
									variant="tertiary"
									type="button"
									onClick={ () =>
										requestNavigation(
											SPECIFICATION_SETS_PATH
										)
									}
								>
									{ __( 'Cancel', 'product-specifications' ) }
								</Button>
								<Button
									variant="primary"
									type="submit"
									isBusy={ editor.isSaving }
									disabled={
										! editor.hasUnsavedChanges ||
										editor.isSaving
									}
									shortcut={ displayShortcut.primary( 's' ) }
								>
									{ __(
										'Save set',
										'product-specifications'
									) }
								</Button>
							</>
						}
					/>
				}
				notices={
					<Notice
						notice={ editor.notice }
						onDismiss={ editor.dismissNotice }
						className="product-specifications-specs-set-editor__notice"
					/>
				}
			>
				<EditorBody editor={ editor } />
			</PageShell>
		</form>
	);
};

export default SpecificationSetEditor;
