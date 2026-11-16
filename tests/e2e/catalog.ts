import type { RequestUtils } from '@wordpress/e2e-test-utils-playwright';

export interface CreatedSpecification {
	id: number;
	name: string;
	slug: string;
}

export interface CreatedSpecificationSet {
	id: number;
	title: string;
	slug: string;
}

export interface CreatedProduct {
	id: number;
	permalink: string;
}

export type ItemSeed = CreatedSpecification & { visible?: boolean };

export interface GroupSeed {
	label: string;
	visible?: boolean;
	items: ItemSeed[];
}

export interface ProductSeed {
	sku?: string;
	set?: CreatedSpecificationSet;
	values?: Record< string, unknown >;
}

export interface StoredStructure {
	groups: Array< { label: string; items: Array< { slug: string } > } >;
}

const SETTINGS_PATH = '/product-specifications/v2/settings';

export class Catalog {
	private readonly cleanups: Array< () => Promise< unknown > > = [];

	constructor(
		private readonly requestUtils: RequestUtils,
		private readonly suffix: string
	) {}

	unique( label: string ): string {
		return `${ label } ${ this.suffix }`;
	}

	async specification(
		name: string,
		type = 'text',
		options: Record< string, unknown > = {}
	): Promise< CreatedSpecification > {
		const record = await this.requestUtils.rest< CreatedSpecification >( {
			method: 'POST',
			path: '/wp/v2/specifications',
			data: {
				name: this.unique( name ),
				specification: { type, options, default: '' },
			},
		} );

		this.track( `/wp/v2/specifications/${ record.id }` );

		return record;
	}

	async specificationSet(
		title: string,
		groups: GroupSeed[]
	): Promise< CreatedSpecificationSet > {
		const uniqueTitle = this.unique( title );
		const record = await this.requestUtils.rest< {
			id: number;
			slug: string;
		} >( {
			method: 'POST',
			path: '/wp/v2/specification-sets',
			data: {
				title: uniqueTitle,
				status: 'publish',
				meta: {
					specification_set_structure: {
						schema_version: 1,
						groups: groups.map( ( group, index ) => ( {
							key: `group-${ index + 1 }`,
							label: group.label,
							description: '',
							visible: group.visible ?? true,
							items: group.items.map( ( item ) => ( {
								slug: item.slug,
								visible: item.visible ?? true,
							} ) ),
						} ) ),
					},
				},
			},
		} );

		this.track( `/wp/v2/specification-sets/${ record.id }` );

		return { id: record.id, title: uniqueTitle, slug: record.slug };
	}

	async product(
		name: string,
		{ sku, set, values = {} }: ProductSeed = {}
	): Promise< CreatedProduct > {
		const record = await this.requestUtils.rest< CreatedProduct >( {
			method: 'POST',
			path: '/wc/v3/products',
			data: {
				name: this.unique( name ),
				type: 'simple',
				status: 'publish',
				regular_price: '10',
				...( sku ? { sku } : {} ),
				meta_data: set
					? [
							{
								key: 'product_specifications_set',
								value: set.slug,
							},
							{
								key: 'product_specifications_values',
								value: { schema_version: 1, values },
							},
					  ]
					: [],
			},
		} );

		this.track( `/wc/v3/products/${ record.id }` );

		return record;
	}

	async page( content: string ): Promise< string > {
		const record = await this.requestUtils.rest< {
			id: number;
			link: string;
		} >( {
			method: 'POST',
			path: '/wp/v2/pages',
			data: { title: this.unique( 'Page' ), content, status: 'publish' },
		} );

		this.track( `/wp/v2/pages/${ record.id }` );

		return record.link;
	}

	async specificationBySlug(
		slug: string
	): Promise< CreatedSpecification | undefined > {
		const records = await this.requestUtils.rest< CreatedSpecification[] >(
			{
				path: '/wp/v2/specifications',
				params: { slug, context: 'edit' },
			}
		);

		return records[ 0 ];
	}

	async structureOf( setId: number ): Promise< StoredStructure > {
		const record = await this.requestUtils.rest< {
			meta: { specification_set_structure: StoredStructure };
		} >( {
			path: `/wp/v2/specification-sets/${ setId }`,
			params: { context: 'edit' },
		} );

		return record.meta.specification_set_structure;
	}

	async storedValuesOf(
		productId: number
	): Promise< Record< string, unknown > > {
		const record = await this.requestUtils.rest< {
			meta_data: Array< { key: string; value: unknown } >;
		} >( { path: `/wc/v3/products/${ productId }` } );
		const stored = record.meta_data.find(
			( meta ) => meta.key === 'product_specifications_values'
		)?.value as { values?: Record< string, unknown > } | undefined;

		return stored?.values ?? {};
	}

	async keepSettings(): Promise< void > {
		const { settings } = await this.requestUtils.rest< {
			settings: Record< string, unknown >;
		} >( { path: SETTINGS_PATH } );

		this.cleanups.push( () =>
			this.requestUtils.rest( {
				method: 'POST',
				path: SETTINGS_PATH,
				data: { settings },
			} )
		);
	}

	adopt( path: string ): void {
		this.track( path );
	}

	async dispose(): Promise< void > {
		for ( const cleanup of this.cleanups.reverse() ) {
			await cleanup().catch( () => undefined );
		}
	}

	private track( path: string ): void {
		this.cleanups.push( () =>
			this.requestUtils.rest( {
				method: 'DELETE',
				path,
				params: { force: true },
			} )
		);
	}
}
