import { __ } from '@wordpress/i18n';
import Layout from '../shared/layout';
import { Button, Flex, FlexItem } from '@wordpress/components';
import { Card, InputControl } from '@wordpress/ui';

import './styles.scss';

const SpecificationSetEditor = () => {
	return (
		<Layout title="Specification Sets">
			<form>
				<div className="product-specifications-specs-set-editor">
					<Flex className="product-specifications-specs-sets__header">
						<FlexItem>
							<h2>
								{ __(
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
								>
									{ __(
										'Save Specification Set',
										'product-specifications'
									) }
								</Button>
							</Flex>
						</FlexItem>
					</Flex>

					<Card.Root className="product-specifications-specs-set-editor__title-card">
						<Card.Header>
							<Card.Title>
								{ __(
									'Specification Set Name',
									'product-specifications'
								) }
							</Card.Title>
						</Card.Header>
						<Card.Content>
							<InputControl
								label={ __(
									'Specification Set Name',
									'product-specifications'
								) }
								description={ __(
									'This name is used for internal organization and will not be visible to customers.',
									'product-specifications'
								) }
								hideLabelFromVision
							/>
						</Card.Content>
					</Card.Root>
					<Card.Root className="product-specifications-specs-set-editor__main">
						<Card.Header>
							<Card.Title>
								{ __(
									'Groups and Specifications',
									'product-specifications'
								) }
							</Card.Title>
						</Card.Header>
						<Card.Content>
							<InputControl
								label={ __(
									'Specification Set Name',
									'product-specifications'
								) }
								description={ __(
									'This name is used for internal organization and will not be visible to customers.',
									'product-specifications'
								) }
								hideLabelFromVision
							/>
						</Card.Content>
					</Card.Root>
				</div>
			</form>
		</Layout>
	);
};

export default SpecificationSetEditor;
