import { groupProgress } from './groupProgress';
import type { SpecificationRecord, StructureGroup } from './types';

const specification = ( id: number, type: string ): SpecificationRecord => ( {
	id,
	name: `spec-${ id }`,
	slug: `spec-${ id }`,
	specification: { type },
} );

const group: StructureGroup = {
	key: 'main',
	label: 'Main',
	description: '',
	visible: true,
	items: [ 1, 2, 3 ].map( ( id ) => ( {
		slug: `spec-${ id }`,
		visible: true,
	} ) ),
};

describe( 'groupProgress', () => {
	it( 'leaves values read from the product out of what is left to fill', () => {
		const progress = groupProgress(
			group,
			{ 'spec-1': 'Cotton' },
			{
				'spec-1': specification( 1, 'text' ),
				'spec-2': specification( 2, 'woocommerce' ),
				'spec-3': specification( 3, 'woocommerce' ),
			}
		);

		expect( progress ).toEqual( { filled: 1, total: 1 } );
	} );
} );
