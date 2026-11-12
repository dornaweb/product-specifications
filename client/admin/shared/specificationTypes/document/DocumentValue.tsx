import apiFetch from '@wordpress/api-fetch';
import { Button, ExternalLink, TextControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import { __, sprintf } from '@wordpress/i18n';
import type { SpecificationTypeValueProps } from '../index';
import {
	asValue,
	storedFiles,
	withLabel,
	withPicked,
	type StoredFile,
} from './documentFiles';
import { canOpenMediaLibrary, openMediaLibrary } from './mediaLibrary';
import './styles.scss';

interface Details {
	title: string;
	url: string;
}

interface MediaRecord {
	id: number;
	title?: { rendered?: string };
	source_url?: string;
}

// A file counts as missing only when the media library answered without it. A failed
// request marks nothing, so a file that exists is never shown as gone.
const useDetails = ( files: StoredFile[] ) => {
	const [ details, setDetails ] = useState< Record< number, Details > >( {} );
	const [ checked, setChecked ] = useState< Set< number > >( new Set() );
	const uncheckedKey = files
		.map( ( file ) => file.id )
		.filter( ( id ) => ! details[ id ] && ! checked.has( id ) )
		.join( ',' );

	useEffect( () => {
		if ( uncheckedKey === '' ) {
			return;
		}

		apiFetch< MediaRecord[] >( {
			path: `/wp/v2/media?include=${ uncheckedKey }&per_page=100&_fields=id,title,source_url`,
		} )
			.then( ( records ) => {
				setDetails( ( current ) => ( {
					...current,
					...Object.fromEntries(
						records.map( ( record ) => [
							record.id,
							{
								title: decodeEntities(
									record.title?.rendered ?? ''
								),
								url: record.source_url ?? '',
							},
						] )
					),
				} ) );
				setChecked(
					( current ) =>
						new Set( [
							...current,
							...uncheckedKey.split( ',' ).map( Number ),
						] )
				);
			} )
			.catch( () => undefined );
	}, [ uncheckedKey ] );

	const isMissing = ( id: number ) => checked.has( id ) && ! details[ id ];

	const remember = ( picked: PickedAttachment[] ) =>
		setDetails( ( current ) => ( {
			...current,
			...Object.fromEntries(
				picked.map( ( attachment ) => [
					attachment.id,
					{ title: attachment.title, url: attachment.url },
				] )
			),
		} ) );

	return { details, isMissing, remember };
};

const DocumentValue = ( {
	label,
	value,
	onChange,
}: SpecificationTypeValueProps ) => {
	const files = storedFiles( value );
	const { details, isMissing, remember } = useDetails( files );

	const add = () =>
		openMediaLibrary( ( picked ) => {
			remember( picked );
			onChange(
				asValue(
					withPicked(
						files,
						picked.map( ( attachment ) => attachment.id )
					)
				)
			);
		} );

	return (
		<div className="ps-documents">
			{ files.length > 0 && (
				<ul className="ps-documents__list">
					{ files.map( ( file ) => {
						const known = details[ file.id ];

						return (
							<li key={ file.id } className="ps-documents__file">
								<span className="ps-documents__name">
									{ known ? (
										<ExternalLink href={ known.url }>
											{ known.title }
										</ExternalLink>
									) : (
										isMissing( file.id ) &&
										__(
											'No longer in the media library',
											'product-specifications'
										)
									) }
								</span>
								<TextControl
									__nextHasNoMarginBottom
									__next40pxDefaultSize
									label={ sprintf(
										/* translators: %s: the specification name. */
										__(
											'Label shown for this file in %s',
											'product-specifications'
										),
										label
									) }
									hideLabelFromVision
									placeholder={
										known?.title ??
										__( 'Label', 'product-specifications' )
									}
									value={ file.label ?? '' }
									onChange={ ( next ) =>
										onChange(
											asValue(
												withLabel(
													files,
													file.id,
													next
												)
											)
										)
									}
								/>
								<Button
									variant="tertiary"
									isDestructive
									onClick={ () =>
										onChange(
											asValue(
												files.filter(
													( other ) =>
														other.id !== file.id
												)
											)
										)
									}
								>
									{ __( 'Remove', 'product-specifications' ) }
								</Button>
							</li>
						);
					} ) }
				</ul>
			) }
			<Button
				variant="secondary"
				disabled={ ! canOpenMediaLibrary() }
				onClick={ add }
			>
				{ __( 'Add files', 'product-specifications' ) }
			</Button>
		</div>
	);
};

export default DocumentValue;
