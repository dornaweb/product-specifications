import { Button, SearchControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { Field } from '../../ui';
import type { Suggestion } from '../index';
import { valueKey } from './spelling';

const SEARCH_FROM = 10;

interface Props {
	learned: Suggestion[];
	onPromote: ( value: string ) => void;
}

const LearnedValues = ( { learned, onPromote }: Props ) => {
	const [ search, setSearch ] = useState( '' );
	const term = valueKey( search );
	const shown = learned.filter( ( suggestion ) =>
		valueKey( suggestion.value ).includes( term )
	);

	return (
		<Field
			label={ __( 'Learned values', 'product-specifications' ) }
			help={ __(
				'Typed on products and suggested while any product carries them. Promote a value to keep suggesting it.',
				'product-specifications'
			) }
			wide
		>
			{ () => (
				<div className="ps-learned-values">
					{ learned.length >= SEARCH_FROM && (
						<SearchControl
							__nextHasNoMarginBottom
							label={ __(
								'Search learned values',
								'product-specifications'
							) }
							value={ search }
							onChange={ setSearch }
						/>
					) }
					<ul className="ps-learned-values__list">
						{ shown.map( ( suggestion ) => (
							<li
								key={ suggestion.value }
								className="ps-learned-values__item"
							>
								<span className="ps-learned-values__value">
									{ suggestion.value }
								</span>
								<span className="ps-learned-values__count">
									{ sprintf(
										/* translators: %d: how many products carry the value. */
										_n(
											'%d product',
											'%d products',
											suggestion.count,
											'product-specifications'
										),
										suggestion.count
									) }
								</span>
								<Button
									variant="secondary"
									size="small"
									label={ sprintf(
										/* translators: %s: a learned value. */
										__(
											'Promote “%s” to a suggestion',
											'product-specifications'
										),
										suggestion.value
									) }
									showTooltip={ false }
									onClick={ () =>
										onPromote( suggestion.value )
									}
								>
									{ __(
										'Promote',
										'product-specifications'
									) }
								</Button>
							</li>
						) ) }
					</ul>
				</div>
			) }
		</Field>
	);
};

export default LearnedValues;
