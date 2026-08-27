import { __ } from '@wordpress/i18n';
import { TabPanel } from '@wordpress/components';
import { Card } from '@wordpress/ui';
import type { SpecificationSetEditorData } from '../hooks/useSpecificationSetEditor';
import GeneralTab from './GeneralTab';
import PlaceholderTab from './PlaceholderTab';
import type {
	EditorPlaceholderTabName,
	EditorTab,
	EditorTabName,
} from './types';

const TABS: EditorTab[] = [
	{
		name: 'general',
		title: __( 'General', 'product-specifications' ),
		className:
			'product-specifications-specs-set-editor__tab product-specifications-specs-set-editor__tab--general',
	},
	{
		name: 'groups',
		title: __( 'Groups & Specifications', 'product-specifications' ),
		className: 'product-specifications-specs-set-editor__tab',
	},
	{
		name: 'assignments',
		title: __( 'Assignments', 'product-specifications' ),
		className: 'product-specifications-specs-set-editor__tab',
	},
	{
		name: 'comparison',
		title: __( 'Comparison', 'product-specifications' ),
		className: 'product-specifications-specs-set-editor__tab',
	},
	{
		name: 'preview',
		title: __( 'Preview', 'product-specifications' ),
		className: 'product-specifications-specs-set-editor__tab',
	},
	{
		name: 'advanced',
		title: __( 'Advanced', 'product-specifications' ),
		className: 'product-specifications-specs-set-editor__tab',
	},
];

interface EditorTabsProps {
	editor: SpecificationSetEditorData;
}

const EditorTabs = ( { editor }: EditorTabsProps ) => (
	<Card.Root className="product-specifications-specs-set-editor__panel">
		<Card.Content>
			<TabPanel
				className="product-specifications-specs-set-editor__tabs"
				activeClass="product-specifications-specs-set-editor__tab--active"
				tabs={ TABS }
			>
				{ ( tab ) => {
					const tabName = tab.name as EditorTabName;

					return (
						<div className="product-specifications-specs-set-editor__tab-panel">
							{ tabName === 'general' ? (
								<GeneralTab
									general={ editor.general }
									updateTextField={ editor.updateTextField }
									updateStatus={ editor.updateStatus }
								/>
							) : (
								<PlaceholderTab
									tab={ tabName as EditorPlaceholderTabName }
								/>
							) }
						</div>
					);
				} }
			</TabPanel>
		</Card.Content>
	</Card.Root>
);

export default EditorTabs;
