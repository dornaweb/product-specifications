import { __ } from '@wordpress/i18n';
import { Card, CardBody, TabPanel } from '@wordpress/components';
import type { SpecificationSetEditorData } from '../hooks/useSpecificationSetEditor';
import GeneralTab from './GeneralTab';
import StructureTab from './StructureTab';
import type { EditorTab, EditorTabName } from './types';

const TABS: EditorTab[] = [
	{
		name: 'general',
		title: __( 'General', 'product-specifications' ),
		className:
			'product-specifications-specs-set-editor__tab product-specifications-specs-set-editor__tab--general',
	},
	{
		name: 'specifications',
		title: __( 'Specifications', 'product-specifications' ),
		className: 'product-specifications-specs-set-editor__tab',
	},
];

interface EditorTabsProps {
	editor: SpecificationSetEditorData;
}

const EditorTabs = ( { editor }: EditorTabsProps ) => (
	<Card className="product-specifications-specs-set-editor__panel">
		<CardBody>
			<TabPanel
				className="product-specifications-specs-set-editor__tabs"
				tabs={ TABS }
				initialTabName="specifications"
			>
				{ ( tab ) => {
					const tabName = tab.name as EditorTabName;

					return (
						<div className="product-specifications-specs-set-editor__tab-panel">
							{ tabName === 'general' && (
								<GeneralTab
									general={ editor.general }
									updateTextField={ editor.updateTextField }
									updateStatus={ editor.updateStatus }
								/>
							) }
							{ tabName === 'specifications' && (
								<StructureTab editor={ editor } />
							) }
						</div>
					);
				} }
			</TabPanel>
		</CardBody>
	</Card>
);

export default EditorTabs;
