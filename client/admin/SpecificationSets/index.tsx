import { __ } from '@wordpress/i18n';
import Layout from '../shared/layout';
import { Button, Flex, FlexItem, Icon } from '@wordpress/components';
import { getNewPath, navigateTo } from '@woocommerce/navigation';
import { useSpecificationSets } from './hooks/useSpecificationSets';
import SpecificationSetsTable from './components/SpecificationSetsTable';
import EmptyState from './components/EmptyState';

const CREATE_URL = getNewPath(
	{},
	'/dwps-product-specifications/specification-set/new'
);

const handleCreateClick = ( event: React.MouseEvent< HTMLAnchorElement > ) => {
	event.preventDefault();
	navigateTo( { url: CREATE_URL } );
};

const SpecificationSetPage = () => {
	const tableProps = useSpecificationSets();
	const isEmpty = ! tableProps.isLoading && tableProps.records.length === 0;

	return (
		<Layout title="Specification Sets">
			<div className="product-specifications-specs-sets">
				<Flex className="product-specifications-specs-sets__header">
					<FlexItem>
						<h2>
							{ __(
								'Specification Sets',
								'product-specifications'
							) }
						</h2>
						<p>
							{ __(
								'Organize and manage reusable groups of specifications for your WooCommerce products.',
								'product-specifications'
							) }
						</p>
					</FlexItem>

					<FlexItem>
						<Button
							variant="primary"
							href={ CREATE_URL }
							onClick={ handleCreateClick }
							className="product-specifications-specs-sets__create-button"
						>
							<Icon icon="plus" />
							{ __( 'Create Set', 'product-specifications' ) }
						</Button>
					</FlexItem>
				</Flex>

				{ isEmpty ? (
					<EmptyState
						createUrl={ CREATE_URL }
						onCreateClick={ handleCreateClick }
					/>
				) : (
					<SpecificationSetsTable { ...tableProps } />
				) }
			</div>
		</Layout>
	);
};

export default SpecificationSetPage;
