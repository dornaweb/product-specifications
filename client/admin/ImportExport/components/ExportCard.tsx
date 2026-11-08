import {
	Button,
	Card,
	CardBody,
	CardFooter,
	CardHeader,
	CheckboxControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

const ExportCard = () => {
	const [ withValues, setWithValues ] = useState( true );
	const exportUrl = window.dwspecsAdmin?.exportUrl ?? '';

	return (
		<Card>
			<CardHeader>
				<h2 className="ps-transfer__title">
					{ __( 'Export', 'product-specifications' ) }
				</h2>
			</CardHeader>
			<CardBody className="ps-transfer__body">
				<p>
					{ __(
						'Download every specification and specification set as one file, to import into another store or keep as a backup.',
						'product-specifications'
					) }
				</p>
				<CheckboxControl
					__nextHasNoMarginBottom
					label={ __(
						'Include product values',
						'product-specifications'
					) }
					help={ __(
						'Each product is identified by its SKU and slug, so its values can be matched on another store.',
						'product-specifications'
					) }
					checked={ withValues }
					onChange={ setWithValues }
				/>
			</CardBody>
			<CardFooter justify="flex-end">
				<Button
					variant="primary"
					disabled={ exportUrl === '' }
					onClick={ () =>
						window.location.assign(
							addQueryArgs( exportUrl, {
								values: withValues ? '1' : '0',
							} )
						)
					}
				>
					{ __( 'Download export', 'product-specifications' ) }
				</Button>
			</CardFooter>
		</Card>
	);
};

export default ExportCard;
