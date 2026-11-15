import {
	parseChoices,
	choicesText,
	choiceSelectOptions,
} from './ChoiceOptionsPanel';

// The textarea is the only place choices are entered, so this pair is the whole
// boundary between what the merchant types and what is stored.
describe( 'parseChoices', () => {
	it( 'drops blank lines and surrounding whitespace', () => {
		expect( parseChoices( ' 5.5"\n\n  6.1"  \n\n' ) ).toEqual( [
			'5.5"',
			'6.1"',
		] );
	} );

	it( 'reads an empty textarea as no choices', () => {
		expect( parseChoices( '   \n\n' ) ).toEqual( [] );
	} );

	it( 'survives a round trip', () => {
		const choices = [ '5.5"', '6.1"', '6.7"' ];

		expect( parseChoices( choicesText( { values: choices } ) ) ).toEqual(
			choices
		);
	} );
} );

describe( 'choicesText', () => {
	it.each( [
		[ 'options belonging to another type', { units: [ 'mm' ] } ],
		[ 'a stored value that is not a list', { values: 'nope' } ],
		[ 'no options at all', {} ],
	] )( 'renders nothing for %s', ( _label, options ) => {
		expect( choicesText( options ) ).toBe( '' );
	} );
} );

describe( 'choiceSelectOptions', () => {
	it( 'leads with the empty option, then the choices in order', () => {
		expect(
			choiceSelectOptions( [ '5.5"', '6.1"' ], '', 'No value' )
		).toEqual( [
			{ value: '', label: 'No value' },
			{ value: '5.5"', label: '5.5"' },
			{ value: '6.1"', label: '6.1"' },
		] );
	} );

	it( 'lists a current value no longer among the choices instead of dropping it', () => {
		expect( choiceSelectOptions( [ '5.5"' ], '6.1"', 'No value' ) ).toEqual(
			[
				{ value: '', label: 'No value' },
				{ value: '6.1"', label: '6.1" — no longer an option' },
				{ value: '5.5"', label: '5.5"' },
			]
		);
	} );

	it( 'does not treat the empty current value as orphaned', () => {
		expect( choiceSelectOptions( [ '5.5"' ], '', 'No value' ) ).toEqual( [
			{ value: '', label: 'No value' },
			{ value: '5.5"', label: '5.5"' },
		] );
	} );
} );
