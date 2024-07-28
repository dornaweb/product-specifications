<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\AttributeGroupsListUi;

use Amiut\ProductSpecs\Repository\AttributeGroupsRepository;
use Amiut\ProductSpecs\Template\TemplateRenderer;

/**
 * @psalm-import-type CollectionArguments from AttributeGroupsRepository
 */
final class AttributeGroupsListPage
{
    private TemplateRenderer $renderer;
    private AttributeGroupsRepository $repository;

    public function __construct(TemplateRenderer $renderer, AttributeGroupsRepository $repository)
    {
        $this->renderer = $renderer;
        $this->repository = $repository;
    }

    public function render(): void
    {
        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $this->renderer->render(
            'admin/attribute-groups/list-page',
            [
                'collection' => $this->repository->findCollection(
                    $this->collectionArguments()
                ),
                'searchKeyword' => $this->searchKeyword(),
            ]
        );
        // phpcs:enable
    }

    /**
     * @return CollectionArguments
     */
    private function collectionArguments(): array
    {
        $currentPage = absint(
            filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT)
        ) ?: 1;

        /** @var CollectionArguments $arguments */
        $arguments = [
            'per_page' => absint(get_option('dwps_view_per_page')) ?: self::DEFAULT_PER_PAGE,
            'search_query' => $this->searchKeyword(),
            'current_page' => $currentPage,
        ];

        return $arguments;
    }

    private function searchKeyword(): string
    {
        return (string) filter_input(INPUT_GET, 'q', FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
