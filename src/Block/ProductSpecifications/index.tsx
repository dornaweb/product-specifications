import { useBlockProps } from '@wordpress/block-editor';
import {
	registerBlockType,
	type BlockConfiguration,
	type BlockEditProps,
} from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';
import './style.scss';

const Edit = ( {
	attributes,
}: BlockEditProps< Record< string, unknown > > ) => (
	<div { ...useBlockProps() }>
		<ServerSideRender block={ metadata.name } attributes={ attributes } />
	</div>
);

registerBlockType( metadata as BlockConfiguration, {
	edit: Edit,
	save: () => null,
} );
