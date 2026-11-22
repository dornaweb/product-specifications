import { registerBlockType, type BlockConfiguration } from '@wordpress/blocks';
import Edit from './Edit';
import metadata from './block.json';
import './style.scss';

registerBlockType( metadata as BlockConfiguration, {
	edit: Edit,
	save: () => null,
} );
