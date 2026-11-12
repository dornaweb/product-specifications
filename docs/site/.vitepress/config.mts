import { defineConfig } from 'vitepress';

export default defineConfig( {
	lang: 'en-US',
	title: 'Product Specifications',
	description:
		'Specification tables for WooCommerce products: define specifications once, group them into sets, and show them on every product page.',
	cleanUrls: true,
	themeConfig: {
		nav: [
			{ text: 'Guide', link: '/guide/getting-started' },
			{ text: 'Upgrading from 0.x', link: '/guide/upgrading' },
			{ text: 'Developers', link: '/developers/' },
		],
		sidebar: {
			'/guide/': [
				{
					text: 'Introduction',
					items: [
						{ text: 'Getting started', link: '/guide/getting-started' },
						{ text: 'Upgrading from 0.x', link: '/guide/upgrading' },
					],
				},
				{
					text: 'Using the plugin',
					items: [
						{ text: 'Specifications', link: '/guide/specifications' },
						{ text: 'Specification types', link: '/guide/types' },
						{ text: 'Specification sets', link: '/guide/sets' },
						{ text: 'Product values', link: '/guide/products' },
						{ text: 'On your storefront', link: '/guide/storefront' },
						{ text: 'Import and export', link: '/guide/import-export' },
						{ text: 'FAQ', link: '/guide/faq' },
					],
				},
			],
			'/developers/': [
				{
					text: 'Developers',
					items: [
						{ text: 'Overview', link: '/developers/' },
						{ text: 'PHP API', link: '/developers/php-api' },
						{ text: 'Hooks', link: '/developers/hooks' },
						{ text: 'Custom specification types', link: '/developers/types' },
						{ text: 'Import and export sections', link: '/developers/import-export' },
					],
				},
			],
		},
		search: { provider: 'local' },
		socialLinks: [
			{ icon: 'github', link: 'https://github.com/dornaweb/product-specifications' },
		],
		footer: {
			message: 'Released under the GPL-2.0-or-later license.',
		},
	},
} );
